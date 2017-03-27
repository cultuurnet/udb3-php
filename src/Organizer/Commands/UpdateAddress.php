<?php

namespace CultuurNet\UDB3\Organizer\Commands;

use CultuurNet\UDB3\Address\Address;

class UpdateAddress extends AbstractUpdateOrganizerCommand
{
    /**
     * @var Address
     */
    private $address;

    /**
     * UpdateAddress constructor.
     * @param string $organizerId
     * @param Address $address
     */
    public function __construct(
        $organizerId,
        Address $address
    ) {
        parent::__construct($organizerId);
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
