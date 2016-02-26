<?php
/**
 * @file
 */

namespace CultuurNet\UDB3;

use Broadway\Serializer\SerializerInterface;
use Broadway\Serializer\SimpleInterfaceSerializer;
use CultuurNet\UDB3\Event\Events\EventImportedFromUDB2;
use CultuurNet\UDB3\Event\Events\DescriptionTranslated;
use CultuurNet\UDB3\Event\Events\LabelAdded;
use CultuurNet\UDB3\Event\Events\LabelsMerged;
use CultuurNet\UDB3\Event\Events\LabelDeleted;
use CultuurNet\UDB3\Event\Events\TitleTranslated;
use CultuurNet\UDB3\EventSourcing\PayloadManipulatingSerializer;
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
         * TRANSLATION EVENTS
         */

        $payloadManipulatingSerializer->manipulateEventsOfClass(
            'CultuurNet\UDB3\Event\TitleTranslated',
            function (array $serializedObject) {
                $serializedObject['class'] = TitleTranslated::class;

                $serializedObject = self::manipulateItemId($serializedObject);

                return $serializedObject;
            }
        );

        $payloadManipulatingSerializer->manipulateEventsOfClass(
            'CultuurNet\UDB3\Event\DescriptionTranslated',
            function (array $serializedObject) {
                $serializedObject['class'] = DescriptionTranslated::class;

                $serializedObject = self::manipulateItemId($serializedObject);

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

                $serializedObject = self::manipulateItemId($serializedObject);

                return $serializedObject;
            }
        );

        $payloadManipulatingSerializer->manipulateEventsOfClass(
            'CultuurNet\UDB3\Event\EventWasTagged',
            function (array $serializedObject) {
                $serializedObject['class'] = LabelAdded::class;

                $serializedObject = self::manipulateItemId($serializedObject);

                $serializedObject = self::manipulateLabel($serializedObject);

                return $serializedObject;
            }
        );

        $payloadManipulatingSerializer->manipulateEventsOfClass(
            'CultuurNet\UDB3\Event\TagErased',
            function (array $serializedObject) {
                $serializedObject['class'] = LabelDeleted::class;

                $serializedObject = self::manipulateItemId($serializedObject);

                $serializedObject = self::manipulateLabel($serializedObject);

                return $serializedObject;
            }
        );

        $payloadManipulatingSerializer->manipulateEventsOfClass(
            'CultuurNet\UDB3\Event\Events\Unlabelled',
            function (array $serializedObject) {
                $serializedObject['class'] = LabelDeleted::class;

                $serializedObject = self::manipulateItemId($serializedObject);

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

                $serializedObject = self::manipulateLabel($serializedObject);

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
         * BOOKING INFO
         */

        $payloadManipulatingSerializer->manipulateEventsOfClass(
            'CultuurNet\UDB3\Event\Events\BookingInfoUpdated',
            function (array $serializedObject) {

                $serializedObject = self::manipulateItemId($serializedObject);

                return $serializedObject;
            }
        );

        /**
         * BOOKING INFO
         */

        $payloadManipulatingSerializer->manipulateEventsOfClass(
            'CultuurNet\UDB3\Event\Events\TypicalAgeRangeDeleted',
            function (array $serializedObject) {

                $serializedObject = self::manipulateItemId($serializedObject);

                return $serializedObject;
            }
        );

        return $payloadManipulatingSerializer;
    }

    /**
     * @param array $serializedObject
     * @return array
     */
    private static function manipulateItemId(array $serializedObject)
    {
        $eventId = $serializedObject['payload']['event_id'];
        $serializedObject['payload']['item_id'] = $eventId;
        unset($serializedObject['payload']['event_id']);

        return $serializedObject;
    }

    /**
     * @param array $serializedObject
     * @return array
     */
    private static function manipulateLabel(array $serializedObject)
    {
        $keyword = $serializedObject['payload']['keyword'];
        $serializedObject['payload']['label'] = $keyword;
        unset($serializedObject['payload']['keyword']);

        return $serializedObject;
    }
}
