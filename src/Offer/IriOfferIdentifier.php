<?php

namespace CultuurNet\UDB3\Offer;

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
     * @param OfferType $type
     */
    public function __construct(
        $iri,
        OfferType $type
    ) {
        $this->iri = $iri;
        $this->type = $type;

        // Remove any trailing slashes to be safe.
        $iri = rtrim($iri, '/');

        // Split the iri into multiple pieces.
        $exploded = explode('/', $iri);

        // The id is the last of all the separate pieces.
        $this->id = array_pop($exploded);
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
    function jsonSerialize()
    {
        return [
            '@id' => $this->iri,
            '@type' => $this->type->toNative(),
        ];
    }
}
