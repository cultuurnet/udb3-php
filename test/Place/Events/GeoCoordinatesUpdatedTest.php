<?php

namespace CultuurNet\UDB3\Place\Events;

use CultuurNet\Geocoding\Coordinate\Coordinates;
use CultuurNet\Geocoding\Coordinate\Latitude;
use CultuurNet\Geocoding\Coordinate\Longitude;

class GeoCoordinatesUpdatedTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_can_be_serialized_and_deserialized()
    {
        $event = new GeoCoordinatesUpdated(
            'f281bc85-3ee4-43a7-b42d-a8982ec9bbc4',
            new Coordinates(
                new Latitude(0.00456),
                new Longitude(-1.24567)
            )
        );

        $expectedArray = [
            'item_id' => 'f281bc85-3ee4-43a7-b42d-a8982ec9bbc4',
            'coordinates' => [
                'lat' => 0.00456,
                'long' => -1.24567,
            ],
        ];

        $actualArray = $event->serialize();

        $deserialized = GeoCoordinatesUpdated::deserialize($actualArray);

        $this->assertEquals($expectedArray, $actualArray);
        $this->assertEquals($event, $deserialized);
    }
}
