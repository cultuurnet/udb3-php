<?php

namespace CultuurNet\UDB3\Place\Events;

use CultuurNet\UDB3\Address\Address;
use CultuurNet\UDB3\Language;
use CultuurNet\UDB3\Place\PlaceEvent;

class AddressTranslated extends PlaceEvent
{
    /**
     * @var Address
     */
    private $address;

    /**
     * @var Language
     */
    private $language;

    /**
     * @param string $placeId
     * @param Address $address
     * @param Language $language
     */
    public function __construct($placeId, Address $address, Language $language)
    {
        parent::__construct($placeId);
        $this->address = $address;
        $this->language = $language;
    }

    /**
     * @return Address
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @return Language
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * @return array
     */
    public function serialize()
    {
        return parent::serialize() + [
            'address' => $this->address->serialize(),
            'language' => $this->language->getCode(),
        ];
    }

    /**
     * @param array $data
     * @return AddressTranslated
     */
    public static function deserialize(array $data)
    {
        return new static(
            $data['place_id'],
            Address::deserialize($data['address']),
            new Language($data['language'])
        );
    }
}
