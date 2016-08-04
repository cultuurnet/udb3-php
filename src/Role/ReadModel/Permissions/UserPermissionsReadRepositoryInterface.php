<?php

namespace CultuurNet\UDB3\Role\ReadModel\Permissions;

use ValueObjects\String\String as StringLiteral;

interface UserPermissionsReadRepositoryInterface
{
    public function getPermissions(StringLiteral $userId);
}
