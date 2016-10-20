<?php

namespace CultuurNet\UDB3;

use Broadway\Serializer\SerializerInterface;
use Broadway\Serializer\SimpleInterfaceSerializer;
use CultuurNet\UDB3\Event\Events\BookingInfoUpdated as EventBookingInfoUpdated;
use CultuurNet\UDB3\Event\Events\ContactPointUpdated as EventContactPointUpdated;
use CultuurNet\UDB3\Event\Events\DescriptionTranslated;
use CultuurNet\UDB3\Event\Events\DescriptionUpdated as EventDescriptionUpdated;
use CultuurNet\UDB3\Event\Events\EventDeleted;
use CultuurNet\UDB3\Event\Events\EventImportedFromUDB2;
use CultuurNet\UDB3\Event\Events\LabelAdded;
use CultuurNet\UDB3\Event\Events\LabelDeleted;
use CultuurNet\UDB3\Event\Events\LabelsMerged;
use CultuurNet\UDB3\Event\Events\MajorInfoUpdated;
use CultuurNet\UDB3\Event\Events\OrganizerDeleted as EventOrganizerDeleted;
use CultuurNet\UDB3\Event\Events\OrganizerUpdated as EventOrganizerUpdated;
use CultuurNet\UDB3\Event\Events\TitleTranslated;
use CultuurNet\UDB3\Event\Events\TypicalAgeRangeDeleted as EventTypicalAgeRangeDeleted;
use CultuurNet\UDB3\Event\Events\TypicalAgeRangeUpdated as EventTypicalAgeRangeUpdated;
use CultuurNet\UDB3\EventSourcing\PayloadManipulatingSerializer;
use CultuurNet\UDB3\Place\Events\BookingInfoUpdated as PlaceBookingInfoUpdated;
use CultuurNet\UDB3\Place\Events\ContactPointUpdated as PlaceContactPointUpdated;
use CultuurNet\UDB3\Place\Events\DescriptionUpdated as PlaceDescriptionUpdated;
use CultuurNet\UDB3\Place\Events\OrganizerDeleted as PlaceOrganizerDeleted;
use CultuurNet\UDB3\Place\Events\OrganizerUpdated as PlaceOrganizerUpdated;
use CultuurNet\UDB3\Place\Events\PlaceDeleted;
use CultuurNet\UDB3\Place\Events\TypicalAgeRangeDeleted as PlaceTypicalAgeRangeDeleted;
use CultuurNet\UDB3\Place\Events\TypicalAgeRangeUpdated as PlaceTypicalAgeRangeUpdated;
use CultuurNet\UDB3\UsedLabelsMemory\Created as UsedLabelsMemoryCreated;
use CultuurNet\UDB3\UsedLabelsMemory\LabelUsed;

/**
 * Factory chaining together the logic to manipulate the payload of old events
 * in order to make it usable by new events.
 *
 * Some cases:
 * - changing the class name / namespace after class renames
 * - changing the names of properties
 */
class BackwardsCompatiblePayloadSerializerFactory
{

    private function __construct()
    {

    }

    /**
     * @return SerializerInterface
     */
    public static function createSerializer()
    {
        $payloadManipulatingSerializer = new PayloadManipulatingSerializer(
            new SimpleInterfaceSerializer()
        );

        /*
         * KEYWORDS EVENTS
         */

        $payloadManipulatingSerializer->manipulateEventsOfClass(
            'CultuurNet\UDB3\UsedKeywordsMemory\Created',
            function (array $serializedObject) {
                $serializedObject['class'] = UsedLabelsMemoryCreated::class;

                return $serializedObject;
            }
        );

        $payloadManipulatingSerializer->manipulateEventsOfClass(
            'CultuurNet\UDB3\UsedKeywordsMemory\KeywordUsed',
            function (array $serializedObject) {
                $serializedObject['class'] = LabelUsed::class;

                $serializedObject = self::replaceKeywordWithLabel($serializedObject);

                return $serializedObject;
            }
        );

        /*
         * TRANSLATION EVENTS
         */

        $payloadManipulatingSerializer->manipulateEventsOfClass(
            'CultuurNet\UDB3\Event\TitleTranslated',
            function (array $serializedObject) {
                $serializedObject['class'] = TitleTranslated::class;

                $serializedObject = self::replaceEventIdWithItemId($serializedObject);

                return $serializedObject;
            }
        );

        $payloadManipulatingSerializer->manipulateEventsOfClass(
            'CultuurNet\UDB3\Event\DescriptionTranslated',
            function (array $serializedObject) {
                $serializedObject['class'] = DescriptionTranslated::class;

                $serializedObject = self::replaceEventIdWithItemId($serializedObject);

                return $serializedObject;
            }
        );

        /*
         * LABEL EVENTS
         */

        $payloadManipulatingSerializer->manipulateEventsOfClass(
            'CultuurNet\UDB3\Event\Events\EventWasLabelled',
            function (array $serializedObject) {
                $serializedObject['class'] = LabelAdded::class;

                $serializedObject = self::replaceEventIdWithItemId($serializedObject);

                return $serializedObject;
            }
        );

        $payloadManipulatingSerializer->manipulateEventsOfClass(
            'CultuurNet\UDB3\Event\EventWasTagged',
            function (array $serializedObject) {
                $serializedObject['class'] = LabelAdded::class;

                $serializedObject = self::replaceEventIdWithItemId($serializedObject);

                $serializedObject = self::replaceKeywordWithLabel($serializedObject);

                return $serializedObject;
            }
        );

        $payloadManipulatingSerializer->manipulateEventsOfClass(
            'CultuurNet\UDB3\Event\TagErased',
            function (array $serializedObject) {
                $serializedObject['class'] = LabelDeleted::class;

                $serializedObject = self::replaceEventIdWithItemId($serializedObject);

                $serializedObject = self::replaceKeywordWithLabel($serializedObject);

                return $serializedObject;
            }
        );

        $payloadManipulatingSerializer->manipulateEventsOfClass(
            'CultuurNet\UDB3\Event\Events\Unlabelled',
            function (array $serializedObject) {
                $serializedObject['class'] = LabelDeleted::class;

                $serializedObject = self::replaceEventIdWithItemId($serializedObject);

                return $serializedObject;
            }
        );

        $payloadManipulatingSerializer->manipulateEventsOfClass(
            'CultuurNet\UDB3\Event\Events\LabelsApplied',
            function (array $serializedObject) {
                $serializedObject['class'] = LabelsMerged::class;

                $keywordsString = $serializedObject['payload']['keywords_string'];

                $query = array();
                parse_str($keywordsString, $query);

                $keywords = explode(';', $query['keywords']);
                $visibles = explode(';', $query['visibles']);

                $labelsArray = array();

                foreach ($keywords as $key => $keyword) {
                    $visible = 'true' === $visibles[$key];
                    $labelsArray[] = new Label(
                        $keyword,
                        $visible
                    );
                }

                $labels = array_map(
                    function (Label $label) {
                        return [
                            'text' => (string) $label,
                            'visible' => $label->isVisible(),
                        ];
                    },
                    $labelsArray
                );

                $serializedObject['payload']['labels'] = $labels;
                unset($serializedObject['payload']['keywords_string']);

                return $serializedObject;
            }
        );

        /**
         * UBD2 IMPORT
         */

        $payloadManipulatingSerializer->manipulateEventsOfClass(
            'CultuurNet\UDB3\Event\EventImportedFromUDB2',
            function (array $serializedObject) {
                $serializedObject['class'] = EventImportedFromUDB2::class;

                return $serializedObject;
            }
        );

        /**
         * EventEvent to AbstractEvent (Offer)
         */
        $refactoredEventEvents = [
            EventBookingInfoUpdated::class,
            EventTypicalAgeRangeDeleted::class,
            EventTypicalAgeRangeUpdated::class,
            EventContactPointUpdated::class,
            MajorInfoUpdated::class,
            EventOrganizerUpdated::class,
            EventOrganizerDeleted::class,
            EventDescriptionUpdated::class,
            EventDeleted::class,
        ];

        foreach ($refactoredEventEvents as $refactoredEventEvent) {
            $payloadManipulatingSerializer->manipulateEventsOfClass(
                $refactoredEventEvent,
                function (array $serializedObject) use ($refactoredEventEvent){
                    $serializedObject = self::replaceEventIdWithItemId($serializedObject);

                    if ($refactoredEventEvent instanceof MajorInfoUpdated) {
                        $prefixer = self::getLocationKeyPrefixer();
                        $serializedObject = $prefixer($serializedObject);
                    }

                    return $serializedObject;
                }
            );
        }

        /**
         * PlaceEvent to AbstractEvent (Offer)
         */
        $refactoredPlaceEvents = [
            PlaceOrganizerUpdated::class,
            PlaceOrganizerDeleted::class,
            PlaceBookingInfoUpdated::class,
            PlaceTypicalAgeRangeDeleted::class,
            PlaceTypicalAgeRangeUpdated::class,
            PlaceContactPointUpdated::class,
            PlaceDescriptionUpdated::class,
            PlaceDeleted::class,
        ];

        foreach ($refactoredPlaceEvents as $refactoredPlaceEvent) {
            $payloadManipulatingSerializer->manipulateEventsOfClass(
                $refactoredPlaceEvent,
                function (array $serializedObject) {
                    $serializedObject = self::replacePlaceIdWithItemId($serializedObject);
                    return $serializedObject;
                }
            );
        }

        /**
         * Place
         */
        $payloadManipulatingSerializer->manipulateEventsOfClass(
            'CultuurNet\UDB3\Place\Events\PlaceCreated',
            self::getAddressKeyPrefixer()
        );
        $payloadManipulatingSerializer->manipulateEventsOfClass(
            'CultuurNet\UDB3\Place\Events\MajorInfoUpdated',
            self::getAddressKeyPrefixer()
        );

        /**
         * Event
         */
        $payloadManipulatingSerializer->manipulateEventsOfClass(
            'CultuurNet\UDB3\Event\Events\EventCreated',
            self::getLocationKeyPrefixer()
        );

        return $payloadManipulatingSerializer;
    }

    private static function getAddressKeyPrefixer()
    {
        return function (array $serializedObject) {
            $serializedObject = self::replaceAddressKey(
                'locality',
                'addressLocality',
                $serializedObject
            );

            $serializedObject = self::replaceAddressKey(
                'country',
                'addressCountry',
                $serializedObject
            );

            return $serializedObject;
        };
    }


    private static function getLocationKeyPrefixer()
    {
        return function (array $serializedObject) {
            $serializedObject = self::replaceLocationKey(
                'locality',
                'addressLocality',
                $serializedObject
            );

            $serializedObject = self::replaceLocationKey(
                'country',
                'addressCountry',
                $serializedObject
            );

            return $serializedObject;
        };
    }

    /**
     * @param array $serializedObject
     * @return array
     */
    private static function replaceEventIdWithItemId(array $serializedObject)
    {
        return self::replaceKeys('event_id', 'item_id', $serializedObject);
    }

    /**
     * @param array $serializedObject
     * @return array
     */
    private static function replacePlaceIdWithItemId(array $serializedObject)
    {
        return self::replaceKeys('place_id', 'item_id', $serializedObject);
    }

    /**
     * @param string $oldKey
     * @param string $newKey
     * @param array $serializedObject
     * @return array
     */
    private static function replaceKeys($oldKey, $newKey, $serializedObject)
    {
        if (isset($serializedObject['payload'][$oldKey])) {
            $value = $serializedObject['payload'][$oldKey];
            $serializedObject['payload'][$newKey] = $value;
            unset($serializedObject['payload'][$oldKey]);
        }

        return $serializedObject;
    }

    /**
     * @param array $serializedObject
     * @return array
     */
    private static function replaceKeywordWithLabel(array $serializedObject)
    {
        $keyword = $serializedObject['payload']['keyword'];
        $serializedObject['payload']['label'] = $keyword;
        unset($serializedObject['payload']['keyword']);

        return $serializedObject;
    }

    /**
     * @param string $oldKey
     * @param string $newKey
     * @param array $serializedObject
     * @return array
     */
    private static function replaceAddressKey($oldKey, $newKey, $serializedObject)
    {
        if (isset($serializedObject['payload']['address'][$oldKey])) {
            $value = $serializedObject['payload']['address'][$oldKey];
            $serializedObject['payload']['address'][$newKey] = $value;
            unset($serializedObject['payload']['address'][$oldKey]);
        }

        return $serializedObject;
    }

    /**
     * @param string $oldKey
     * @param string $newKey
     * @param array $serializedObject
     * @return array
     */
    private static function replaceLocationKey($oldKey, $newKey, $serializedObject)
    {
        if (isset($serializedObject['payload']['location']['address'][$oldKey])) {
            $value = $serializedObject['payload']['location']['address'][$oldKey];
            $serializedObject['payload']['location']['address'][$newKey] = $value;
            unset($serializedObject['payload']['location']['address'][$oldKey]);
        }

        return $serializedObject;
    }
}
