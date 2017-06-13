<?php

namespace CultuurNet\UDB3\Place\Events;

use CultuurNet\UDB3\Address\Address;
use CultuurNet\UDB3\Place\PlaceEvent;

class AddressUpdated extends PlaceEvent
{
    /**
     * @var Address
     */
    private $address;

    /**
     * @param string $placeId
     * @param Address $address
     */
    public function __construct($placeId, Address $address)
    {
        parent::__construct($placeId);
        $this->address = $address;
    }

    /**
     * @return Address
     */
    public function getAddress()
    {
        return $this->address;
    }
}
