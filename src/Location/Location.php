<?php

namespace CultuurNet\UDB3\Location;

use Broadway\Serializer\SerializableInterface;
use CultuurNet\UDB3\Address\Address;
use ValueObjects\StringLiteral\StringLiteral;

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

    public function __construct($cdbid)
    {
        $this->cdbid = $cdbid;
    }

    public function getCdbid()
    {
        return $this->cdbid;
    }

    /**
     * {@inheritdoc}
     */
    public function serialize()
    {
        return [
          'cdbid' => $this->cdbid,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function deserialize(array $data)
    {
        return new static($data['cdbid']);
    }
}
