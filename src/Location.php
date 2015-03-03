<?php

/**
 * @file
 * Contains CultuurNet\UDB3\Location.
 */

namespace CultuurNet\UDB3;

use Broadway\Serializer\SerializableInterface;

/**
 * Instantiates an UDB3 Location.
 */
class Location implements SerializableInterface
{

    /**
     * Cdbid of the connected place.
     * @var string
     */
    protected $cdbid;

    /**
     * @var string
     */
    protected $name;

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
    protected $postalcode;

    /**
     * @var string
     */
    protected $street;

    public function __construct($cdbid, $name, $country, $locality, $postalcode, $street)
    {
        $this->cdbid = $cdbid;
        $this->name = $name;
        $this->country = $country;
        $this->locality = $locality;
        $this->postalcode = $postalcode;
        $this->street = $street;
    }

    public function getCdbid()
    {
        return $this->cdbid;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getCountry()
    {
        return $this->country;
    }

    public function getLocality()
    {
        return $this->locality;
    }

    public function getPostalcode()
    {
        return $this->postalcode;
    }

    public function getStreet()
    {
        return $this->street;
    }

    /**
     * {@inheritdoc}
     */
    public function serialize()
    {
        return [
          'name' => $this->name,
          'address' => [
            'addressCountry' => $this->country,
            'addressLocality' => $this->locality,
            'postalCode' => $this->postalcode,
            'streetAddress' => $this->street,
          ]
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function deserialize(array $data)
    {
        return new static(
                $data['name'], $data['address']['addressCountry'], $data['address']['addressLocality'], $data['address']['postalCode'], $data['address']['streetAddress']
        );
    }
}
