<?php

namespace CultuurNet\UDB3\Offer;

use ValueObjects\Web\Url;

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

    /**
     * @return Url
     */
    public function getIri();
}
