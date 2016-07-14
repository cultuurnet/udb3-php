<?php

namespace CultuurNet\UDB3\Role\Services;

use CultuurNet\UDB3\Role\ValueObjects\Permission;
use ValueObjects\Identity\UUID;

interface RoleReadingServiceInterface
{
    /**
     * @param UUID $uuid
     * @return Permission[]
     */
    public function getPermissionsByRoleUuid(UUID $uuid);
}
