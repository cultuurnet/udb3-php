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
use CultuurNet\UDB3\Event\Events\LabelRemoved;
use CultuurNet\UDB3\Event\Events\MajorInfoUpdated;
use CultuurNet\UDB3\Event\Events\OrganizerDeleted as EventOrganizerDeleted;
use CultuurNet\UDB3\Event\Events\OrganizerUpdated as EventOrganizerUpdated;
use CultuurNet\UDB3\Event\Events\TitleTranslated;
use CultuurNet\UDB3\Event\Events\TypicalAgeRangeDeleted as EventTypicalAgeRangeDeleted;
use CultuurNet\UDB3\Event\Events\TypicalAgeRangeUpdated as EventTypicalAgeRangeUpdated;
use CultuurNet\UDB3\EventSourcing\PayloadManipulatingSerializer;
use CultuurNet\UDB3\Label\ReadModels\JSON\Repository\ReadRepositoryInterface;
use CultuurNet\UDB3\Label\ValueObjects\Visibility;
use CultuurNet\UDB3\Place\Events\BookingInfoUpdated as PlaceBookingInfoUpdated;
use CultuurNet\UDB3\Place\Events\ContactPointUpdated as PlaceContactPointUpdated;
use CultuurNet\UDB3\Place\Events\DescriptionUpdated as PlaceDescriptionUpdated;
use CultuurNet\UDB3\Place\Events\OrganizerDeleted as PlaceOrganizerDeleted;
use CultuurNet\UDB3\Place\Events\OrganizerUpdated as PlaceOrganizerUpdated;
use CultuurNet\UDB3\Place\Events\PlaceDeleted;
use CultuurNet\UDB3\Place\Events\TypicalAgeRangeDeleted as PlaceTypicalAgeRangeDeleted;
use CultuurNet\UDB3\Place\Events\TypicalAgeRangeUpdated as PlaceTypicalAgeRangeUpdated;
use ValueObjects\Identity\UUID;

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
     * @param ReadRepositoryInterface $labelRepository
     * @return SerializerInterface
     */
    public static function createSerializer(ReadRepositoryInterface $labelRepository)
    {
        $payloadManipulatingSerializer = new PayloadManipulatingSerializer(
            new SimpleInterfaceSerializer()
        );

        /*
         * CREATE EVENTS
         *
         */

        $payloadManipulatingSerializer->manipulateEventsOfClass(
            'CultuurNet\UDB3\Event\Events\EventCreated',
            function (array $serializedObject) {
                return self::addDefaultMainLanguage($serializedObject);
            }
        );

        $payloadManipulatingSerializer->manipulateEventsOfClass(
            'CultuurNet\UDB3\Place\Events\PlaceCreated',
            function (array $serializedObject) {
                return self::addDefaultMainLanguage($serializedObject);
            }
        );

        $payloadManipulatingSerializer->manipulateEventsOfClass(
            'CultuurNet\UDB3\Organizer\Events\OrganizerCreatedWithUniqueWebsite',
            function (array $serializedObject) {
                return self::addDefaultMainLanguage($serializedObject);
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
            'CultuurNet\UDB3\Label\Events\MadeInvisible',
            function (array $serializedObject) use ($labelRepository) {
                return self::addLabelName($serializedObject, $labelRepository);
            }
        );

        $payloadManipulatingSerializer->manipulateEventsOfClass(
            'CultuurNet\UDB3\Label\Events\MadeVisible',
            function (array $serializedObject) use ($labelRepository) {
                return self::addLabelName($serializedObject, $labelRepository);
            }
        );

        $payloadManipulatingSerializer->manipulateEventsOfClass(
            'CultuurNet\UDB3\Label\Events\MadePrivate',
            function (array $serializedObject) use ($labelRepository) {
                return self::addLabelName($serializedObject, $labelRepository);
            }
        );

        $payloadManipulatingSerializer->manipulateEventsOfClass(
            'CultuurNet\UDB3\Label\Events\MadePublic',
            function (array $serializedObject) use ($labelRepository) {
                return self::addLabelName($serializedObject, $labelRepository);
            }
        );

        $payloadManipulatingSerializer->manipulateEventsOfClass(
            'CultuurNet\UDB3\Organizer\Events\LabelAdded',
            function (array $serializedObject) use ($labelRepository) {
                return self::fixOrganizerLabelEvent($serializedObject, $labelRepository);
            }
        );

        $payloadManipulatingSerializer->manipulateEventsOfClass(
            'CultuurNet\UDB3\Organizer\Events\LabelRemoved',
            function (array $serializedObject) use ($labelRepository) {
                return self::fixOrganizerLabelEvent($serializedObject, $labelRepository);
            }
        );

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
                $serializedObject['class'] = LabelRemoved::class;

                $serializedObject = self::replaceEventIdWithItemId($serializedObject);

                $serializedObject = self::replaceKeywordWithLabel($serializedObject);

                return $serializedObject;
            }
        );

        $payloadManipulatingSerializer->manipulateEventsOfClass(
            'CultuurNet\UDB3\Event\Events\Unlabelled',
            function (array $serializedObject) {
                $serializedObject['class'] = LabelRemoved::class;

                $serializedObject = self::replaceEventIdWithItemId($serializedObject);

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
         * PLACE FACILITIES EVENT
         */
        $payloadManipulatingSerializer->manipulateEventsOfClass(
            'CultuurNet\UDB3\Place\Events\FacilitiesUpdated',
            function (array $serializedObject) {
                $serializedObject = self::replacePlaceIdWithItemId($serializedObject);

                return $serializedObject;
            }
        );

        /**
         * GEOCOORDINATES UPDATED EVENT
         */
        $payloadManipulatingSerializer->manipulateEventsOfClass(
            'CultuurNet\UDB3\Place\Events\GeoCoordinatesUpdated',
            function (array $serializedObject) {
                $serializedObject = self::replacePlaceIdWithItemId($serializedObject);

                return $serializedObject;
            }
        );

        /**
         * BOOKING INFO EVENT
         */
        $manipulateAvailability = function (array $serializedBookingInfo, $propertyName) {
            if (!isset($serializedBookingInfo[$propertyName]) || empty($serializedBookingInfo[$propertyName])) {
                $serializedBookingInfo[$propertyName] = null;
                return $serializedBookingInfo;
            }

            $dateTimeString = $serializedBookingInfo[$propertyName];

            // The new serialized date time format is a string according the ISO 8601 format.
            // If this is so return without modifications.
            $dateTimeFromAtom = \DateTimeImmutable::createFromFormat(\DATE_ATOM, $dateTimeString);
            if ($dateTimeFromAtom) {
                return $serializedBookingInfo;
            }

            // For older format a modification is needed to ISO 8601 format.
            $dateTimeFromAtomWithMilliseconds = \DateTimeImmutable::createFromFormat(
                'Y-m-d\TH:i:s.uP',
                $dateTimeString
            );
            if ($dateTimeFromAtomWithMilliseconds) {
                $serializedBookingInfo[$propertyName] = $dateTimeFromAtomWithMilliseconds->format(\DATE_ATOM);
                return $serializedBookingInfo;
            }

            // In case of unknown format clear the available date property.
            unset($serializedBookingInfo[$propertyName]);
            return $serializedBookingInfo;
        };

        $manipulateBookingInfoEvent = function (array $serializedEvent) use ($manipulateAvailability) {
            $serializedEvent = self::replaceEventIdWithItemId($serializedEvent);
            $serializedEvent = self::replacePlaceIdWithItemId($serializedEvent);

            $serializedBookingInfo = $serializedEvent['payload']['bookingInfo'];
            $serializedBookingInfo = $manipulateAvailability($serializedBookingInfo, 'availabilityStarts');
            $serializedBookingInfo = $manipulateAvailability($serializedBookingInfo, 'availabilityEnds');
            $serializedEvent['payload']['bookingInfo'] = $serializedBookingInfo;

            return $serializedEvent;
        };

        $payloadManipulatingSerializer->manipulateEventsOfClass(
            EventBookingInfoUpdated::class,
            $manipulateBookingInfoEvent
        );

        $payloadManipulatingSerializer->manipulateEventsOfClass(
            PlaceBookingInfoUpdated::class,
            $manipulateBookingInfoEvent
        );

        /**
         * EventEvent to AbstractEvent (Offer)
         */
        $refactoredEventEvents = [
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
                function (array $serializedObject) {
                    $serializedObject = self::replaceEventIdWithItemId($serializedObject);
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

        return $payloadManipulatingSerializer;
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
     * @param array $serializedObject
     * @param ReadRepositoryInterface $labelRepository
     * @return array
     */
    private static function addLabelName(
        array $serializedObject,
        ReadRepositoryInterface $labelRepository
    ) {
        if (!isset($serializedObject['payload']['name'])) {
            $uuid = $serializedObject['payload']['uuid'];
            $label = $labelRepository->getByUuid(new UUID($uuid));

            $serializedObject['payload']['name'] = $label->getName()->toNative();
        }

        return $serializedObject;
    }

    /**
     * @param array $serializedObject
     * @param ReadRepositoryInterface $labelRepository
     * @return array
     */
    private static function fixOrganizerLabelEvent(
        array $serializedObject,
        ReadRepositoryInterface $labelRepository
    ) {
        if (!isset($serializedObject['payload']['label']) ||
            !isset($serializedObject['payload']['visibility'])) {
            $uuid = $serializedObject['payload']['labelId'];
            $label = $labelRepository->getByUuid(new UUID($uuid));

            $serializedObject['payload']['label'] = $label->getName()->toNative();
            $serializedObject['payload']['visibility'] = $label->getVisibility() === Visibility::VISIBLE();
        }

        return $serializedObject;
    }

    /**
     * @param array $serializedObject
     * @return array
     */
    private static function addDefaultMainLanguage(array $serializedObject)
    {
        if (!isset($serializedObject['payload']['main_language'])) {
            $mainLanguage = new Language('nl');
            $serializedObject['payload']['main_language'] = $mainLanguage->getCode();
        }

        return $serializedObject;
    }
}
