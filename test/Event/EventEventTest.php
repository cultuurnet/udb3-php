<?php

namespace CultuurNet\UDB3\Event;

class EventEventTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @dataProvider serializationDataProvider
     * @param array $expectedSerializedValue
     * @param MockEventEvent $mockEventEvent
     */
    public function it_can_be_serialized_into_an_array(
        $expectedSerializedValue,
        MockEventEvent $mockEventEvent
    ) {
        $this->assertEquals(
            $expectedSerializedValue,
            $mockEventEvent->serialize()
        );
    }

    /**
     * @test
     * @dataProvider serializationDataProvider
     * @param array $serializedValue
     * @param MockEventEvent $expectedMockEventEvent
     */
    public function it_can_be_deserialized_from_an_array(
        $serializedValue,
        MockEventEvent $expectedMockEventEvent
    ) {
        $this->assertEquals(
            $expectedMockEventEvent,
            MockEventEvent::deserialize($serializedValue)
        );
    }

    /**
     * @test
     */
    public function it_throws_an_error_if_a_wrong_type_is_given()
    {
        $this->setExpectedException(
            \InvalidArgumentException::class,
            'Expected eventId to be a string, received integer'
        );
        new MockEventEvent(4);
    }

    /**
     * @test
     */
    public function it_can_return_its_id()
    {
        $eventEvent = new MockEventEvent('testmefoo');
        $expectedEventEventId = 'testmefoo';
        $this->assertEquals($expectedEventEventId, $eventEvent->getEventId());
    }

    public function serializationDataProvider()
    {
        return [
            'mockEventEvent' => [
                [
                    'event_id' => 'foo',
                ],
                new MockEventEvent(
                    'foo'
                ),
            ],
        ];
    }
}
