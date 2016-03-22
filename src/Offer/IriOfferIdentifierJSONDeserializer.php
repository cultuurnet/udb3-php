<?php

namespace CultuurNet\UDB3\Offer;

use CultuurNet\Deserializer\DeserializerInterface;
use CultuurNet\Deserializer\MissingValueException;
use CultuurNet\Deserializer\NotWellFormedException;
use ValueObjects\String\String;

class IriOfferIdentifierJSONDeserializer implements DeserializerInterface
{
    /**
     * @var IriOfferIdentifierFactoryInterface
     */
    private $iriOfferIdentifierFactory;

    /**
     * IriOfferIdentifierJSONDeserializer constructor.
     * @param IriOfferIdentifierFactoryInterface $iriOfferIdentifierFactory
     */
    public function __construct(IriOfferIdentifierFactoryInterface $iriOfferIdentifierFactory)
    {
        $this->iriOfferIdentifierFactory = $iriOfferIdentifierFactory;
    }

    /**
     * @param String $data
     * @return IriOfferIdentifier
     */
    public function deserialize(String $data)
    {
        $data = json_decode($data->toNative(), true);

        if (null === $data) {
            throw new NotWellFormedException('Invalid JSON');
        }

        if (!isset($data['@id'])) {
            throw new MissingValueException('Missing property "@id".');
        }
        //@TODO III-826 Remove type property.
        if (!isset($data['@type'])) {
            throw new MissingValueException('Missing property "@type".');
        }

        return $this->iriOfferIdentifierFactory->fromIri(
            $data['@id']
        );
    }
}
