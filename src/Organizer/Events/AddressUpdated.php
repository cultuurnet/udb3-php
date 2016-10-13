<?php

namespace CultuurNet\UDB3\Organizer\Events;

use CultuurNet\UDB3\Address\Address;

class AddressUpdated extends OrganizerEvent
{
    /**
     * @var Address
     */
    private $address;

    /**
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
