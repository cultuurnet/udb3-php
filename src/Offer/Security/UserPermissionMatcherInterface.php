<?php

namespace CultuurNet\UDB3\Offer\Security;

use CultuurNet\UDB3\Role\ValueObjects\Permission;
use ValueObjects\String\String as StringLiteral;

interface UserPermissionMatcherInterface
{
    /**
     * @param StringLiteral $userId
     * @param Permission $permission
     * @param StringLiteral $offerId
     * @return bool
     */
    public function itMatchesOffer(
        StringLiteral $userId,
        Permission $permission,
        StringLiteral $offerId
    );
}
