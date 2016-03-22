<?php

namespace CultuurNet\UDB3\Place\ReadModel\JSONLD;

use CultuurNet\UDB3\Iri\IriGeneratorInterface;
use CultuurNet\UDB3\Place\Events\PlaceProjectedToJSONLD;
use CultuurNet\UDB3\ReadModel\DocumentEventFactory;

class EventFactory implements DocumentEventFactory
{
    /**
     * @param IriGeneratorInterface $iriGenerator
     */
    public function __construct(IriGeneratorInterface $iriGenerator)
    {
        $this->iriGenerator = $iriGenerator;
    }

    /**
     * @inheritdoc
     */
    public function createEvent($id)
    {
        return new PlaceProjectedToJSONLD(
            $this->iriGenerator->iri($id)
        );
    }
}
