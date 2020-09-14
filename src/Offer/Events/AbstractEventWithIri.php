<?php

namespace CultuurNet\UDB3\Offer\Events;

use Broadway\Serializer\SerializableInterface;

abstract class AbstractEventWithIri extends AbstractEvent implements SerializableInterface
{
    /**
     * @var string
     */
    private $iri;

    /**
     * @param string $itemId
     * @param string $iri
     */
    final public function __construct(string $itemId, $iri)
    {
        parent::__construct($itemId);
        $this->iri = (string) $iri;
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
        return parent::serialize() + array(
            'iri' => $this->iri,
        );
    }

    /**
     * @param array $data
     * @return static
     */
    public static function deserialize(array $data)
    {
        return new static($data['item_id'], $data['iri']);
    }
}
