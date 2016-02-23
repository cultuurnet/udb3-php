<?php

namespace CultuurNet\UDB3\Offer;

interface OfferIdentifierInterface extends \JsonSerializable, \Serializable
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
