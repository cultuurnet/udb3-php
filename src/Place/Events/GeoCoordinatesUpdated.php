<?php

namespace CultuurNet\UDB3\Place\Events;

use CultuurNet\Geocoding\Coordinate\Coordinates;
use CultuurNet\UDB3\Offer\Events\AbstractEvent;

class GeoCoordinatesUpdated extends AbstractEvent
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
}
