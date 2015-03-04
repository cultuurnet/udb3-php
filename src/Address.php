<?php

/**
 * @file
 * Contains CultuurNet\UDB3\Address.
 */

namespace CultuurNet\UDB3;

use Broadway\Serializer\SerializableInterface;

/**
 * Value object for address information.
 */
class Address implements SerializableInterface
{

    /**
     * @var string
     */
    protected $country;

    /**
     * @var string
     */
    protected $locality;

    /**
     * @var string
     */
    protected $postalCode;

    /**
     * @var string
     */
    protected $streetAddress;


    public function __construct($streetAddress, $postalCode, $locality, $country)
    {
        $this->streetAddress = $streetAddress;
        $this->postalCode = $postalCode;
        $this->locality = $locality;
        $this->country = $country;
    }

    public function getCountry()
    {
        return $this->country;
    }

    public function getLocality()
    {
        return $this->locality;
    }

    public function getPostalCode()
    {
        return $this->postalCode;
    }

    public function getStreetAddress()
    {
        return $this->streetAddress;
    }

    /**
     * {@inheritdoc}
     */
    public function serialize()
    {
        return [
          'streetAddress' => $this->streetAddress,
          'postalCode' => $this->postalCode,
          'locality' => $this->locality,
          'country' => $this->country,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function deserialize(array $data)
    {
        return new static(
                $data['streetAddress'], $data['postalCode'], $data['locality'], $data['country']
        );
    }
}
