<?php

namespace CultuurNet\UDB3;

use Broadway\Serializer\SerializableInterface;
use ValueObjects\Geography\Country;
use ValueObjects\String\String as StringLiteral;

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
     * City/Town/Village
     * @var StringLiteral
     */
    protected $locality;

    /**
     * Postal code/P.O. Box/ZIP code
     * @var StringLiteral
     */
    protected $postalCode;

    /**
     * Street name and number
     * @var StringLiteral
     */
    protected $streetAddress;

    public function __construct(
        StringLiteral $streetAddress,
        StringLiteral $postalCode,
        StringLiteral $locality,
        Country $country)
    {
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
     * @return StringLiteral
     */
    public function getLocality()
    {
        return $this->locality;
    }

    /**
     * @return StringLiteral
     */
    public function getPostalCode()
    {
        return $this->postalCode;
    }

    /**
     * @return StringLiteral
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
            new StringLiteral($data['streetAddress']),
            new StringLiteral($data['postalCode']),
            new StringLiteral($data['locality']),
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
