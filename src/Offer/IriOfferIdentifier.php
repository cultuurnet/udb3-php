<?php

namespace CultuurNet\UDB3\Offer;

use CultuurNet\UDB3\Variations\Command\ValidationException;

class IriOfferIdentifier implements OfferIdentifierInterface
{
    /**
     * @var string
     */
    private $iri;

    /**
     * @var string
     */
    private $id;

    /**
     * @var OfferType
     */
    private $type;

    /**
     * @param string $iri
     * @param string $id
     * @param OfferType $type
     */
    public function __construct(
        $iri,
        $id,
        OfferType $type
    ) {
        $this->iri = $iri;
        $this->type = $type;
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getIri()
    {
        return $this->iri;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return OfferType
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        return [
            '@id' => $this->iri,
            '@type' => $this->type->toNative(),
        ];
    }

    /**
     * @return string
     */
    public function serialize()
    {
        return json_encode(
            [
                'iri' => $this->iri,
                'id' => $this->id,
                'type' => $this->type->toNative(),
            ]
        );
    }

    /**
     * @param string $serialized
     */
    public function unserialize($serialized)
    {
        $data = json_decode($serialized, true);
        $this->iri = $data['iri'];
        $this->id = $data['id'];
        $this->type = OfferType::fromNative($data['type']);
    }
}
