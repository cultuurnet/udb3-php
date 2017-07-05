<?php

namespace CultuurNet\UDB3\Event\Events;

use CultuurNet\UDB3\BookingInfo;

class BookingInfoUpdatedTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @dataProvider serializationDataProvider
     * @param array $expectedSerializedValue
     * @param BookingInfoUpdated $bookingInfoUpdated
     */
    public function it_can_be_serialized_into_an_array(
        $expectedSerializedValue,
        BookingInfoUpdated $bookingInfoUpdated
    ) {
        $this->assertEquals(
            $expectedSerializedValue,
            $bookingInfoUpdated->serialize()
        );
    }

    /**
     * @test
     * @dataProvider serializationDataProvider
     * @param array $serializedValue
     * @param BookingInfoUpdated $expectedBookingInfoUpdated
     */
    public function it_can_be_deserialized_from_an_array(
        $serializedValue,
        BookingInfoUpdated $expectedBookingInfoUpdated
    ) {
        $this->assertEquals(
            $expectedBookingInfoUpdated,
            BookingInfoUpdated::deserialize($serializedValue)
        );
    }

    public function serializationDataProvider()
    {
        return [
            'bookingInfoUpdated' => [
                [
                    'item_id' => 'foo',
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
                new BookingInfoUpdated(
                    'foo',
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
