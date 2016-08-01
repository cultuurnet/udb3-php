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
     * @return string
     */
    public function create(StringLiteral $name);

    /**
     * Rename a role.
     *
     * @param UUID $uuid
     * @param StringLiteral $name
     * @return string
     */
    public function rename(UUID $uuid, StringLiteral $name);

    /**
     * Add a permission to a role.
     *
     * @param UUID $uuid
     * @param Permission $permission
     * @return string
     */
    public function addPermission(UUID $uuid, Permission $permission);

    /**
     * Remove a permission from a role.
     *
     * @param UUID $uuid
     * @param Permission $permission
     * @return string
     */
    public function removePermission(UUID $uuid, Permission $permission);

    /**
     * @param UUID $uuid
     * @param StringLiteral $userId
     * @return string
     */
    public function addUser(UUID $uuid, StringLiteral $userId);

    /**
     * @param UUID $uuid
     * @param StringLiteral $userId
     * @return string
     */
    public function removeUser(UUID $uuid, StringLiteral $userId);

    /**
     * Setting a constraint on a role.
     *
     * @param UUID $uuid
     * @param StringLiteral $query
     * @return string
     */
    public function setConstraint(UUID $uuid, StringLiteral $query);

    /**
     * Deleting a role.
     *
     * @param UUID $uuid
     * @return string
     */
    public function delete(UUID $uuid);
}
