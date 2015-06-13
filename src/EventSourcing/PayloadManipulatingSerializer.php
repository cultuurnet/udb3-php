<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\EventSourcing;

use Assert\Assertion;
use Broadway\Serializer\SerializerInterface;
use Broadway\Serializer\SimpleInterfaceSerializer;

/**
 * Decorates a SimpleInterfaceSerializer, first maps old class names to new
 * class names.
 */
class PayloadManipulatingSerializer implements SerializerInterface
{
    /**
     * @var callable[]
     */
    private $manipulations;

    /**
     * @param SimpleInterfaceSerializer $serializer
     */
    public function __construct(SimpleInterfaceSerializer $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * @inheritdoc
     */
    public function serialize($object)
    {
        return $this->serializer->serialize($object);
    }

    /**
     * @param string $oldClassName
     * @param callable $callback
     */
    public function manipulateEventsOfClass($className, callable $callback)
    {
        if (isset($this->manipulations[$className])) {
            throw new \RuntimeException(
                "Manipulation on events of class {$className} already added, " .
                "can add only one."
            );
        }
        $this->manipulations[$className] = $callback;
    }

    /**
     * @inheritdoc
     */
    public function deserialize(array $serializedObject)
    {
        $manipulatedSerializedObject = $this->manipulate($serializedObject);

        return $this->serializer->deserialize($manipulatedSerializedObject);
    }

    /**
     * @param array $serializedObject
     * @return array
     */
    private function manipulate($serializedObject)
    {
        Assertion::keyExists(
            $serializedObject,
            'class',
            "Key 'class' should be set."
        );

        $manipulatedSerializedObject = $serializedObject;
        $class = $manipulatedSerializedObject['class'];

        if (isset($this->manipulations[$class])) {
            $manipulatedSerializedObject = $this->manipulations[$class]($manipulatedSerializedObject);
        }

        return $manipulatedSerializedObject;
    }
}
