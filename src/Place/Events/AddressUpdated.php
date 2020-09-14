<?php

namespace CultuurNet\UDB3\Place\Events;

use CultuurNet\UDB3\Address\Address;
use CultuurNet\UDB3\Place\PlaceEvent;

final class AddressUpdated extends PlaceEvent
{
    /**
     * @var Address
     */
    private $address;

    /**
     * @param string $placeId
     * @param Address $address
     */
    public function __construct(string $placeId, Address $address)
    {
        parent::__construct($placeId);
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
     * @return AddressUpdated
     */
    public static function deserialize(array $data): AddressUpdated
    {
        return new static($data['place_id'], Address::deserialize($data['address']));
    }
}
