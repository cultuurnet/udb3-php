<?php

namespace CultuurNet\UDB3\Offer\ReadModel\Permission;

use ValueObjects\String\String as StringLiteral;

interface PermissionRepositoryInterface
{
    /**
     * @param StringLiteral $offerId
     * @param StringLiteral $uitId
     * @return void
     */
    public function markOfferEditableByUser(
        StringLiteral $offerId,
        StringLiteral $uitId
    );
}
