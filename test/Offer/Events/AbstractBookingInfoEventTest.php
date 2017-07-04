<?php

namespace CultuurNet\UDB3\Offer\Events;

use CultuurNet\UDB3\BookingInfo;

class AbstractBookingInfoEventTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AbstractBookingInfoEvent
     */
    protected $abstractBookingInfoEvent;

    /**
     * @var string
     */
    protected $itemId;

    /**
     * @var BookingInfo
     */
    protected $bookingInfo;

    public function setUp()
    {
        $this->itemId = 'Foo';
        $this->bookingInfo = new BookingInfo(
            'http://foo.bar',
            'urlLabel',
            '0123456789',
            'foo@bar.com',
            '01/01/2016',
            '31/01/2016',
            'name',
            'description'
        );
        $this->abstractBookingInfoEvent = new MockAbstractBookingInfoEvent(
            $this->itemId,
            $this->bookingInfo
        );
    }

    /**
     * @test
     */
    public function it_can_be_instantiated_With_properties()
    {
        $expectedItemId = 'Foo';
        $expectedBookingInfo = new BookingInfo(
            'http://foo.bar',
            'urlLabel',
            '0123456789',
            'foo@bar.com',
            '01/01/2016',
            '31/01/2016',
            'name',
            'description'
        );
        $expectedAbstractBookingInfoEvent = new MockAbstractBookingInfoEvent(
            $expectedItemId,
            $expectedBookingInfo
        );

        $this->assertEquals($expectedAbstractBookingInfoEvent, $this->abstractBookingInfoEvent);
    }

    /**
     * @test
     */
    public function it_can_return_its_properties()
    {
        $expectedItemId = 'Foo';
        $expectedBookingInfo = new BookingInfo(
            'http://foo.bar',
            'urlLabel',
            '0123456789',
            'foo@bar.com',
            '01/01/2016',
            '31/01/2016',
            'name',
            'description'
        );

        $itemId = $this->abstractBookingInfoEvent->getItemId();
        $bookingInfo = $this->abstractBookingInfoEvent->getBookingInfo();

        $this->assertEquals($expectedItemId, $itemId);
        $this->assertEquals($expectedBookingInfo, $bookingInfo);
    }

    /**
     * @test
     * @dataProvider serializationDataProvider
     * @param $expectedSerializedValue
     * @param MockAbstractBookingInfoEvent $bookingInfoEvent
     */
    public function it_can_be_serialized_to_an_array(
        $expectedSerializedValue,
        MockAbstractBookingInfoEvent $bookingInfoEvent
    ) {
        $this->assertEquals(
            $expectedSerializedValue,
            $bookingInfoEvent->serialize()
        );
    }

    /**
     * @test
     * @dataProvider serializationDataProvider
     * @param $serializedValue
     * @param MockAbstractBookingInfoEvent $expectedBookingInfoEvent
     */
    public function it_can_deserialize_an_array(
        $serializedValue,
        MockAbstractBookingInfoEvent $expectedBookingInfoEvent
    ) {
        $this->assertEquals(
            $expectedBookingInfoEvent,
            MockAbstractBookingInfoEvent::deserialize($serializedValue)
        );
    }

    /**
     * @return array
     */
    public function serializationDataProvider()
    {
        return [
            'abstractBookingInfoEvent' => [
                [
                    'item_id' => 'madId',
                    'bookingInfo' => [
                        'phone' => '0123456789',
                        'email' => 'foo@bar.com',
                        'url' => 'http://foo.bar',
                        'urlLabel' => 'urlLabel',
                        'name' => 'name',
                        'description' => 'description',
                        'availabilityStarts' => '01/01/2016',
                        'availabilityEnds' => '31/01/2016',
                    ],
                ],
                new MockAbstractBookingInfoEvent(
                    'madId',
                    new BookingInfo(
                        'http://foo.bar',
                        'urlLabel',
                        '0123456789',
                        'foo@bar.com',
                        '01/01/2016',
                        '31/01/2016',
                        'name',
                        'description'
                    )
                ),
            ],
        ];
    }
}
