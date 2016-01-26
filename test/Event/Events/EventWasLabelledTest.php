<?php

namespace test\Event\Events;

use Broadway\Serializer\SerializableInterface;
use CultuurNet\UDB3\Event\Events\LabelAdded;
use CultuurNet\UDB3\Label;

class EventWasLabelledTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_can_be_serialized_to_an_array()
    {
        $labelsMerged = new LabelAdded(
            'foo',
            new Label('label 1')
        );

        $this->assertInstanceOf(SerializableInterface::class, $labelsMerged);

        $expectedSerializedEvent = [
            'item_id' => 'foo',
            'label' => 'label 1',
        ];

        $this->assertEquals(
            $expectedSerializedEvent,
            $labelsMerged->serialize()
        );
    }

    /**
     * @test
     */
    public function it_can_deserialize_an_array()
    {
        $serializedEvent = [
            'item_id' => 'foo',
            'label' => 'label 1',
        ];

        $expectedEventWasLabelled = new LabelAdded(
            'foo',
            new Label('label 1')
        );

        $this->assertEquals(
            $expectedEventWasLabelled,
            LabelAdded::deserialize($serializedEvent)
        );
    }
}
