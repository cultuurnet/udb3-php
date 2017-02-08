<?php

namespace CultuurNet\UDB3\Place\Commands;

use CultuurNet\UDB3\Address\Address;
use CultuurNet\UDB3\Offer\Commands\AbstractCommand;

class UpdateGeoCoordinatesFromAddress extends AbstractCommand
{
    /**
     * @var Address
     */
    private $address;

    /**
     * @param string $itemId
     * @param Address $address
     */
    public function __construct($itemId, Address $address)
    {
        parent::__construct($itemId);
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
