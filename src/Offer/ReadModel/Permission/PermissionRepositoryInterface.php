<?php

namespace CultuurNet\UDB3\Offer\ReadModel\Permission;

use ValueObjects\String\String;

interface PermissionRepositoryInterface
{
    /**
     * @param String $offerId
     * @param String $uitId
     * @return void
     */
    public function markOfferEditableByUser(String $offerId, String $uitId);
}
