<?php

namespace CultuurNet\UDB3\Offer\Events;

use CultuurNet\Geocoding\Coordinate\Coordinates;
use CultuurNet\Geocoding\Coordinate\Latitude;
use CultuurNet\Geocoding\Coordinate\Longitude;

abstract class AbstractGeoCoordinatesUpdated extends AbstractEvent
{
    /**
     * @var Coordinates
     */
    private $coordinates;

    /**
     * @param string $itemId
     * @param Coordinates $coordinates
     */
    final public function __construct(string $itemId, Coordinates $coordinates)
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
     * @return static
     */
    public static function deserialize(array $data)
    {
        return new static(
            $data['item_id'],
            new Coordinates(
                new Latitude($data['coordinates']['lat']),
                new Longitude($data['coordinates']['long'])
            )
        );
    }
}
