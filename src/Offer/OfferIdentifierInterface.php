<?php

namespace CultuurNet\UDB3\Offer;

interface OfferIdentifierInterface extends \JsonSerializable
{
    /**
     * @return string
     */
    public function getId();

    /**
     * @return OfferType
     */
    public function getType();
}
