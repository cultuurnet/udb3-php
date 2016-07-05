<?php

namespace CultuurNet\UDB3\Role\Services;

use CultuurNet\UDB3\Role\ValueObjects\Permission;
use ValueObjects\Identity\UUID;
use ValueObjects\String\String as StringLiteral;

interface RoleEditingServiceInterface
{
    /**
     * Create a role.
     *
     * @param StringLiteral $name
     * @return mixed
     */
    public function create(StringLiteral $name);

    /**
     * Rename a role.
     *
     * @param UUID $uuid
     * @param StringLiteral $name
     * @return mixed
     */
    public function rename(UUID $uuid, StringLiteral $name);

    /**
     * Add a permission to a role.
     *
     * @param UUID $uuid
     * @param Permission $permission
     * @return mixed
     */
    public function addPermission(UUID $uuid, Permission $permission);

    /**
     * Remove a permission from a role.
     *
     * @param UUID $uuid
     * @param Permission $permission
     * @return mixed
     */
    public function removePermission(UUID $uuid, Permission $permission);

    /**
     * Setting a constraint on a role.
     *
     * @param UUID $uuid
     * @param StringLiteral $query
     * @return mixed
     */
    public function setConstraint(UUID $uuid, StringLiteral $query);
}
