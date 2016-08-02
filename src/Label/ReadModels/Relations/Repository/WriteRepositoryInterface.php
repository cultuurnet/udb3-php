<?php

namespace CultuurNet\UDB3\Label\ReadModels\Relations\Repository;

use CultuurNet\UDB3\Offer\OfferType;
use ValueObjects\Identity\UUID;
use ValueObjects\String\String as StringLiteral;

interface WriteRepositoryInterface
{
    /**
     * @param UUID $uuid
     * @param OfferType $offerType
     * @param StringLiteral $offerId
     */
    public function save(
        UUID $uuid,
        OfferType $offerType,
        StringLiteral $offerId
    );

    /**
     * @param UUID $uuid
     * @param StringLiteral $offerId
     */
    public function deleteByUuidAndOfferId(
        UUID $uuid,
        StringLiteral $offerId
    );
}
