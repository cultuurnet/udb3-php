<?php

namespace CultuurNet\UDB3\Offer\ReadModel\Permission;

use ValueObjects\String\String as StringLiteral;

interface PermissionQueryInterface
{
    /**
     * @param StringLiteral $uitId
     * @return StringLiteral[] A list of offer ids.
     */
    public function getEditableOffers(StringLiteral $uitId);
}
