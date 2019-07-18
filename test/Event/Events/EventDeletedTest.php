<?php

namespace CultuurNet\UDB3\Event\Events;

use PHPUnit\Framework\TestCase;

class EventDeletedTest extends TestCase
{
    /**
     * @test
     * @dataProvider serializationDataProvider
     * @param array $expectedSerializedValue
     * @param EventDeleted $eventDeleted
     */
    public function it_can_be_serialized_into_an_array(
        $expectedSerializedValue,
        EventDeleted $eventDeleted
    ) {
        $this->assertEquals(
            $expectedSerializedValue,
            $eventDeleted->serialize()
        );
    }

    /**
     * @test
     * @dataProvider serializationDataProvider
     * @param array $serializedValue
     * @param EventDeleted $expectedEventDeleted
     */
    public function it_can_be_deserialized_from_an_array(
        $serializedValue,
        EventDeleted $expectedEventDeleted
    ) {
        $this->assertEquals(
            $expectedEventDeleted,
            EventDeleted::deserialize($serializedValue)
        );
    }

    /**
     * @test
     */
    public function it_throws_an_error_if_a_wrong_type_is_given()
    {
        $this->setExpectedException(
            \InvalidArgumentException::class,
            'Expected itemId to be a string, received integer'
        );
        new EventDeleted(4);
    }

    /**
     * @test
     */
    public function it_can_return_its_id()
    {
        $domainEvent = new EventDeleted('testmefoo');
        $expectedEventId = 'testmefoo';
        $this->assertEquals($expectedEventId, $domainEvent->getItemId());
    }

    public function serializationDataProvider()
    {
        return [
            'eventDeleted' => [
                [
                    'item_id' => 'foo',
                ],
                new EventDeleted(
                    'foo'
                ),
            ],
        ];
    }
}
