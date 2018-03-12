<?php

namespace CultuurNet\UDB3\Address;

use Broadway\Serializer\SerializableInterface;
use CultuurNet\UDB3\JsonLdSerializableInterface;
use CultuurNet\UDB3\Model\ValueObject\Geography\Address as Udb3ModelAddress;
use ValueObjects\Geography\Country;
use ValueObjects\Geography\CountryCode;

/**
 * Value object for address information.
 */
class Address implements SerializableInterface, JsonLdSerializableInterface
{
    /**
     * @var string
     */
    protected $countryCode;

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
        $this->countryCode = $country->getCode()->toNative();
    }

    /**
     * @return Country
     */
    public function getCountry()
    {
        return Country::fromNative($this->countryCode);
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
          'addressLocality' => $this->locality->toNative(),
          'addressCountry' => $this->countryCode,
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
            new Locality($data['addressLocality']),
            Country::fromNative($data['addressCountry'])
        );
    }

    /**
     * {@inheritdoc}
     */
    public function toJsonLd()
    {
        return [
            'addressCountry' => $this->countryCode,
            'addressLocality' => $this->locality->toNative(),
            'postalCode' => $this->postalCode->toNative(),
            'streetAddress' => $this->streetAddress->toNative(),
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

    /**
     * @param Udb3ModelAddress $address
     * @return self
     */
    public static function fromUdb3Model(Udb3ModelAddress $address)
    {
        return new self(
            new Street($address->getStreet()->toString()),
            new PostalCode($address->getPostalCode()->toString()),
            new Locality($address->getLocality()->toString()),
            new Country(CountryCode::fromNative($address->getCountryCode()->toString()))
        );
    }
}
