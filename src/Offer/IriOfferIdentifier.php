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
        $this->id = $this->getIdFromIri($iri);
    }

    /**
     * @param string $iri
     * @return string
     */
    private function getIdFromIri($iri)
    {
        // Remove any trailing slashes to be safe.
        $iri = rtrim($iri, '/');

        // Split the iri into multiple pieces.
        $exploded = explode('/', $iri);

        // The id is the last of all the separate pieces.
        return array_pop($exploded);
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
        return json_encode($this->jsonSerialize());
    }

    /**
     * @param string $serialized
     */
    public function unserialize($serialized)
    {
        $data = json_decode($serialized, true);
        $this->iri = $data['@id'];
        $this->type = OfferType::fromNative($data['@type']);
        $this->id = $this->getIdFromIri($this->iri);
    }
}
