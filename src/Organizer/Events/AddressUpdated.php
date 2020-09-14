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
        string $organizerId,
        Address $address
    ) {
        parent::__construct($organizerId);
        $this->address = $address;
    }

    /**
     * @return Address
     */
    public function getAddress(): Address
    {
        return $this->address;
    }

    /**
     * @return array
     */
    public function serialize(): array
    {
        return parent::serialize() + [
            'address' => $this->address->serialize(),
        ];
    }

    /**
     * @param array $data
     * @return static
     */
    public static function deserialize(array $data): self
    {
        return new self(
            $data['organizer_id'],
            Address::deserialize($data['address'])
        );
    }
}
