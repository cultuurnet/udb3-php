<?php

namespace CultuurNet\UDB3\Place\Events;

use CultuurNet\Geocoding\Coordinate\Coordinates;
use CultuurNet\Geocoding\Coordinate\Latitude;
use CultuurNet\Geocoding\Coordinate\Longitude;
use CultuurNet\UDB3\Place\PlaceEvent;

class GeoCoordinatesUpdated extends PlaceEvent
{
    /**
     * @var Coordinates
     */
    private $coordinates;

    /**
     * @param string $itemId
     * @param Coordinates $coordinates
     */
    public function __construct($itemId, Coordinates $coordinates)
    {
        parent::__construct($itemId);
        $this->coordinates = $coordinates;
    }

    /**
     * @return Coordinates
     */
    public function getCoordinates()
    {
        return $this->coordinates;
    }

    /**
     * @return array
     */
    public function serialize()
    {
        return parent::serialize() + [
            'coordinates' => [
                'lat' => $this->coordinates->getLatitude()->toDouble(),
                'long' => $this->coordinates->getLongitude()->toDouble(),
            ],
        ];
    }

    /**
     * @param array $data
     * @return GeoCoordinatesUpdated
     */
    public static function deserialize(array $data)
    {
        return new GeoCoordinatesUpdated(
            $data['place_id'],
            new Coordinates(
                new Latitude($data['coordinates']['lat']),
                new Longitude($data['coordinates']['long'])
            )
        );
    }
}
