<?php

namespace CultuurNet\UDB3\Actor;

class ActorCreatedTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @dataProvider serializationDataProvider
     * @param array $expectedSerializedValue
     * @param ActorCreated $actorCreated
     */
    public function it_can_be_serialized_into_an_array(
        $expectedSerializedValue,
        ActorCreated $actorCreated
    ) {
        $this->assertEquals(
            $expectedSerializedValue,
            $actorCreated->serialize()
        );
    }

    /**
     * @test
     * @dataProvider serializationDataProvider
     * @param array $serializedValue
     * @param ActorCreated $expectedActorCreated
     */
    public function it_can_be_deserialized_from_an_array(
        $serializedValue,
        ActorCreated $expectedActorCreated
    ) {
        $this->assertEquals(
            $expectedActorCreated,
            ActorCreated::deserialize($serializedValue)
        );
    }

    public function serializationDataProvider()
    {
        return [
            'unlabelled' => [
                [
                    'actor_id' => 'actor_id',
                ],
                new ActorCreated(
                    'actor_id'
                ),
            ],
        ];
    }
}
