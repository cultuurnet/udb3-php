<?php

namespace CultuurNet\UDB3\Offer\ReadModel\Permission;

use ValueObjects\String\String;

interface PermissionQueryInterface
{
    /**
     * @param String $uitId
     * @return String[] A list of offer ids.
     */
    public function getEditableOffers(String $uitId);
}
