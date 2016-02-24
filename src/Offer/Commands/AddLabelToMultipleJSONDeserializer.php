<?php

namespace CultuurNet\UDB3\Offer\Commands;

use CultuurNet\Deserializer\DeserializerInterface;
use CultuurNet\Deserializer\JSONDeserializer;
use CultuurNet\Deserializer\MissingValueException;
use CultuurNet\Deserializer\NotWellFormedException;
use CultuurNet\UDB3\Label;
use CultuurNet\UDB3\Offer\OfferIdentifierCollection;
use ValueObjects\String\String;

class AddLabelToMultipleJSONDeserializer extends JSONDeserializer
{
    /**
     * @var DeserializerInterface
     */
    private $offerIdentifierDeserializer;

    /**
     * @param DeserializerInterface $offerIdentifierDeserializer
     */
    public function __construct(DeserializerInterface $offerIdentifierDeserializer)
    {
        $this->offerIdentifierDeserializer = $offerIdentifierDeserializer;
    }

    /**
     * @param \ValueObjects\String\String $data
     *
     * @return AddLabelToMultiple
     *
     * @throws NotWellFormedException
     */
    public function deserialize(String $data)
    {
        $data = parent::deserialize($data);

        if (empty($data->label)) {
            throw new MissingValueException('Missing value "label".');
        }
        if (empty($data->offers)) {
            throw new MissingValueException('Missing value "offers".');
        }

        $label = new Label($data->label);
        $offers = new OfferIdentifierCollection();

        foreach ($data->offers as $offer) {
            $offers = $offers->with(
                $this->offerIdentifierDeserializer->deserialize(
                    new String(
                        json_encode($offer)
                    )
                )
            );
        }

        return new AddLabelToMultiple($offers, $label);
    }
}
