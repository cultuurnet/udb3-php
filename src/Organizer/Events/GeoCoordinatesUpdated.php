<?php declare(strict_types=1);

namespace CultuurNet\UDB3\Organizer\Events;

use CultuurNet\Geocoding\Coordinate\Coordinates;
use CultuurNet\Geocoding\Coordinate\Latitude;
use CultuurNet\Geocoding\Coordinate\Longitude;

class GeoCoordinatesUpdated extends OrganizerEvent
{
    /**
     * @var Coordinates
     */
    private $coordinates;

    public function __construct(string $organizerId, Coordinates $coordinates)
    {
        parent::__construct($organizerId);
        $this->coordinates = $coordinates;
    }

    /**
     * @return Coordinates
     */
    public function coordinates(): Coordinates
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
            $data['organizer_id'],
            new Coordinates(
                new Latitude($data['coordinates']['lat']),
                new Longitude($data['coordinates']['long'])
            )
        );
    }
}
