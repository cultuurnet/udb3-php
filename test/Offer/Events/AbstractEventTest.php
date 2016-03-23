<?php

namespace CultuurNet\UDB3\Offer\Events;

class AbstractEventTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AbstractEvent|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $event;

    /**
     * @var string
     */
    protected $itemId;

    public function setUp()
    {
        $this->itemId = 'Foo';
        $this->event = new MockAbstractEvent($this->itemId);
    }

    /**
     * @test
     */
    public function it_can_be_instantiated_With_properties()
    {
        $expectedItemId = 'Foo';
        $expectedEvent = new MockAbstractEvent($expectedItemId);

        $this->assertEquals($expectedEvent, $this->event);
    }

    /**
     * @test
     */
    public function it_can_return_its_properties()
    {
        $expectedItemId = 'Foo';

        $itemId = $this->event->getItemId();

        $this->assertEquals($expectedItemId, $itemId);
    }

    /**
     * @test
     * @dataProvider serializationDataProvider
     * @param $expectedSerializedValue
     * @param MockAbstractEvent $abstractEvent
     */
    public function it_can_be_serialized_to_an_array(
        $expectedSerializedValue,
        MockAbstractEvent $abstractEvent
    ) {
        $this->assertEquals(
            $expectedSerializedValue,
            $abstractEvent->serialize()
        );
    }

    /**
     * @test
     * @dataProvider serializationDataProvider
     * @param $serializedValue
     * @param MockAbstractEvent $expectedAbstractEvent
     */
    public function it_can_deserialize_an_array(
        $serializedValue,
        MockAbstractEvent $expectedAbstractEvent
    ) {
        $this->assertEquals(
            $expectedAbstractEvent,
            MockAbstractEvent::deserialize($serializedValue)
        );
    }

    /**
     * @return array
     */
    public function serializationDataProvider()
    {
        return [
            'abstractEvent' => [
                [
                    'item_id' => 'madId',
                ],
                new MockAbstractEvent(
                    'madId'
                ),
            ],
        ];
    }
}
