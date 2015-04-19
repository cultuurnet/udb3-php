<?php
/**
 * @file
 */

namespace CultuurNet\UDB3;

use Broadway\Serializer\SerializerInterface;
use Broadway\Serializer\SimpleInterfaceSerializer;
use CultuurNet\UDB3\Event\Events\EventWasLabelled;
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

                $serializedObject['payload']['label'] = $serializedObject['payload']['keyword'];
                unset($serializedObject['payload']['keyword']);

                return $serializedObject;
            }
        );

        $payloadManipulatingSerializer->manipulateEventsOfClass(
            'CultuurNet\UDB3\Event\EventWasTagged',
            function (array $serializedObject) {
                $serializedObject['class'] = EventWasLabelled::class;

                $serializedObject['payload']['label'] = $serializedObject['payload']['keyword'];
                unset($serializedObject['payload']['keyword']);

                return $serializedObject;
            }
        );

        $payloadManipulatingSerializer->manipulateEventsOfClass(
            'CultuurNet\UDB3\Event\TagErased',
            function (array $serializedObject) {
                $serializedObject['class'] = Event\Events\Unlabelled::class;

                $serializedObject['payload']['label'] = $serializedObject['payload']['keyword'];
                unset($serializedObject['payload']['keyword']);

                return $serializedObject;
            }
        );

        return $payloadManipulatingSerializer;
    }
}
