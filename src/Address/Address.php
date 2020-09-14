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
final class Address implements SerializableInterface, JsonLdSerializableInterface
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
    public function getCountry(): Country
    {
        return Country::fromNative($this->countryCode);
    }

    /**
     * @return Locality
     */
    public function getLocality(): Locality
    {
        return $this->locality;
    }

    /**
     * @return PostalCode
     */
    public function getPostalCode(): PostalCode
    {
        return $this->postalCode;
    }

    /**
     * @return Street
     */
    public function getStreetAddress(): Street
    {
        return $this->streetAddress;
    }

    /**
     * {@inheritdoc}
     */
    public function serialize(): array
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
    public static function deserialize(array $data): Address
    {
        return new self(
            new Street($data['streetAddress']),
            new PostalCode($data['postalCode']),
            new Locality($data['addressLocality']),
            Country::fromNative($data['addressCountry'])
        );
    }

    /**
     * {@inheritdoc}
     */
    public function toJsonLd(): array
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
    public function sameAs(Address $otherAddress): bool
    {
        return $this->toJsonLd() === $otherAddress->toJsonLd();
    }

    /**
     * @param Udb3ModelAddress $address
     * @return self
     */
    public static function fromUdb3ModelAddress(Udb3ModelAddress $address): Address
    {
        return new self(
            new Street($address->getStreet()->toString()),
            new PostalCode($address->getPostalCode()->toString()),
            new Locality($address->getLocality()->toString()),
            new Country(CountryCode::fromNative($address->getCountryCode()->toString()))
        );
    }
}
