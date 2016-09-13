<?php

namespace CultuurNet\UDB3;

use Broadway\Serializer\SerializableInterface;
use CultuurNet\UDB3\Address\Address;
use CultuurNet\UDB3\Address\Locality;
use CultuurNet\UDB3\Address\PostalCode;
use CultuurNet\UDB3\Address\Street;
use ValueObjects\Geography\Country;
use ValueObjects\String\String as StringLiteral;

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
     * @var StringLiteral
     */
    protected $name;

    /**
     * @var Address
     */
    protected $address;

    public function __construct($cdbid, StringLiteral $name, Address $address)
    {
        $this->cdbid = $cdbid;
        $this->name = $name;
        $this->address = $address;
    }

    public function getCdbid()
    {
        return $this->cdbid;
    }

    /**
     * @return StringLiteral
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return Address
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * {@inheritdoc}
     */
    public function serialize()
    {
        return [
          'cdbid' => $this->cdbid,
          'name' => $this->name->toNative(),
          'address' => $this->address->serialize()
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function deserialize(array $data)
    {
        return new static(
            $data['cdbid'],
            new StringLiteral($data['name']),
            Address::deserialize($data['address'])
        );
    }
}
