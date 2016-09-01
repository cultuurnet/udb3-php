<?php

namespace CultuurNet\UDB3\Address;

use Broadway\Serializer\SerializableInterface;
use CultuurNet\UDB3\Address\Locality;
use CultuurNet\UDB3\Address\PostalCode;
use CultuurNet\UDB3\Address\Street;
use CultuurNet\UDB3\JsonLdSerializableInterface;
use ValueObjects\Geography\Country;

/**
 * Value object for address information.
 */
class Address implements SerializableInterface, JsonLdSerializableInterface
{
    /**
     * @var Country
     */
    protected $country;

    /**
     * @var Locality
     */
    protected $locality;

    /**
     * @var PostalCode
     */
    protected $postalCode;

    /**
     * @var Street
     */
    protected $streetAddress;

    public function __construct(
        Street $streetAddress,
        PostalCode $postalCode,
        Locality $locality,
        Country $country
    ) {
        $this->streetAddress = $streetAddress;
        $this->postalCode = $postalCode;
        $this->locality = $locality;
        $this->country = $country;
    }

    /**
     * @return Country
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * @return Locality
     */
    public function getLocality()
    {
        return $this->locality;
    }

    /**
     * @return PostalCode
     */
    public function getPostalCode()
    {
        return $this->postalCode;
    }

    /**
     * @return Street
     */
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
          'streetAddress' => $this->streetAddress->toNative(),
          'postalCode' => $this->postalCode->toNative(),
          'locality' => $this->locality->toNative(),
          'country' => $this->country->getCode()->toNative(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function deserialize(array $data)
    {
        return new static(
            new Street($data['streetAddress']),
            new PostalCode($data['postalCode']),
            new Locality($data['locality']),
            Country::fromNative($data['country'])
        );
    }

    /**
     * {@inheritdoc}
     */
    public function toJsonLd()
    {
        return [
            'addressCountry' => $this->country->getCode()->toNative(),
            'addressLocality' => $this->locality->toNative(),
            'postalCode' => $this->postalCode->toNative(),
            'streetAddress' => $this->streetAddress->toNative()
        ];
    }

    /**
     * @param Address $otherAddress
     * @return bool
     */
    public function sameAs(Address $otherAddress)
    {
        return $this->toJsonLd() === $otherAddress->toJsonLd();
    }
}
