<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\UsedLabelsMemory;

use Broadway\Serializer\SerializableInterface;

class CreatedTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_can_be_serialized_to_an_array()
    {
        $created = new Created('123');

        $this->assertInstanceOf(SerializableInterface::class, $created);

        $expectedSerializedEvent = [
            'user_id' => '123',
        ];

        $this->assertEquals(
            $expectedSerializedEvent,
            $created->serialize()
        );
    }

    /**
     * @test
     */
    public function it_can_deserialize_an_array()
    {
        $serializedEvent = [
            'user_id' => '123',
        ];

        $expectedCreated = new Created('123');

        $this->assertEquals(
            $expectedCreated,
            Created::deserialize($serializedEvent)
        );
    }
}
