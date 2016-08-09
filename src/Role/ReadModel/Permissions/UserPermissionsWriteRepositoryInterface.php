<?php

namespace CultuurNet\UDB3\Role\ReadModel\Permissions;

use CultuurNet\UDB3\Role\ValueObjects\Permission;
use ValueObjects\Identity\UUID;

interface UserPermissionsWriteRepositoryInterface
{
    /**
     * @param UUID $roleId
     */
    public function removeRole(UUID $roleId);

    /**
     * @param Permission $permission
     * @param UUID $roleId
     */
    public function addRolePermission(UUID $roleId, Permission $permission);

    /**
     * @param Permission $permission
     * @param UUID $roleId
     */
    public function removeRolePermission(UUID $roleId, Permission $permission);

    /**
     * @param string $userId
     * @param UUID $roleId
     */
    public function addUserRole($userId, UUID $roleId);

    /**
     * @param string $userId
     * @param UUID $roleId
     */
    public function removeUserRole($userId, UUID $roleId);
}
