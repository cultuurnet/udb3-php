<?php

namespace CultuurNet\UDB3\Offer;

interface IriOfferIdentifierFactoryInterface
{
    /**
     * @param string $iri
     * @return IriOfferIdentifier
     */
    public function fromIri($iri);
}
