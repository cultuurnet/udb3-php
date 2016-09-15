<?php

namespace CultuurNet\UDB3\Role\ReadModel\Constraints;

use CultuurNet\UDB3\Role\ValueObjects\Permission;
use ValueObjects\String\String as StringLiteral;

interface UserConstraintsReadRepositoryInterface
{
    /**
     * @param StringLiteral $userId
     * @param Permission $permission
     * @return StringLiteral[]
     */
    public function getByUserAndPermission(
        StringLiteral $userId,
        Permission $permission
    );
}
