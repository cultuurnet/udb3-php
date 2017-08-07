<?php

namespace CultuurNet\UDB3\Event\Commands;

use CultuurNet\UDB3\Location\Location;
use CultuurNet\UDB3\Offer\Commands\AbstractCommand;

class UpdateLocation extends AbstractCommand
{
    /**
     * @var Location
     */
    private $location;

    /**
     * UpdateLocation constructor.
     * @param string $itemId
     * @param Location $location
     */
    public function __construct(
        $itemId,
        Location $location
    ) {
        parent::__construct($itemId);

        $this->location = $location;
    }

    /**
     * @return Location
     */
    public function getLocation()
    {
        return $this->location;
    }
}
