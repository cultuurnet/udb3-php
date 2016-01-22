<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\UsedLabelsMemory;

use Broadway\Serializer\SerializableInterface;
use CultuurNet\UDB3\Label;

class LabelUsedTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_can_be_serialized_to_an_array()
    {
        $labelUsed = new LabelUsed('123', new Label('foo'));

        $this->assertInstanceOf(SerializableInterface::class, $labelUsed);

        $expectedSerializedEvent = [
            'user_id' => '123',
            'label' => 'foo',
        ];

        $this->assertEquals(
            $expectedSerializedEvent,
            $labelUsed->serialize()
        );
    }

    /**
     * @test
     */
    public function it_can_deserialize_an_array()
    {
        $serializedEvent = [
            'user_id' => '123',
            'label' => 'foo',
        ];

        $expectedLabelUsed = new LabelUsed('123', new Label('foo'));

        $this->assertEquals(
            $expectedLabelUsed,
            LabelUsed::deserialize($serializedEvent)
        );
    }
}
