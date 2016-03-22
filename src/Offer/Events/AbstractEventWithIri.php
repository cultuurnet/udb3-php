<?php

namespace CultuurNet\UDB3\Offer\Events;

use Broadway\Serializer\SerializableInterface;

abstract class AbstractEventWithIri implements SerializableInterface
{
    /**
     * @var string
     */
    private $iri;

    /**
     * @param string $iri
     */
    public function __construct($iri)
    {
        $this->iri = $iri;
    }

    /**
     * @return string
     */
    public function getIri()
    {
        return $this->iri;
    }

    /**
     * @return array
     */
    public function serialize()
    {
        return array(
            'iri' => $this->iri
        );
    }

    /**
     * @param array $data
     * @return static
     */
    public static function deserialize(array $data)
    {
        return new static($data['iri']);
    }
}
