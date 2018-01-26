<?php

namespace CultuurNet\UDB3\Event\Events;

use CultuurNet\Geocoding\Coordinate\Coordinates;
use CultuurNet\Geocoding\Coordinate\Latitude;
use CultuurNet\Geocoding\Coordinate\Longitude;
use CultuurNet\UDB3\Event\EventEvent;

class GeoCoordinatesUpdated extends EventEvent
{
    /**
     * @var Coordinates
     */
    private $coordinates;

    /**
     * @param string $eventId
     * @param Coordinates $coordinates
     */
    public function __construct($eventId, Coordinates $coordinates)
    {
        parent::__construct($eventId);
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
            $data['event_id'],
            new Coordinates(
                new Latitude($data['coordinates']['lat']),
                new Longitude($data['coordinates']['long'])
            )
        );
    }
}
