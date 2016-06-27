<?php

namespace CultuurNet\UDB3\Role;

use Broadway\EventSourcing\EventSourcedAggregateRoot;
use CultuurNet\UDB3\Role\Events\PermissionAdded;
use CultuurNet\UDB3\Role\Events\PermissionRemoved;
use CultuurNet\UDB3\Role\Events\RoleCreated;
use CultuurNet\UDB3\Role\Events\RoleRenamed;
use CultuurNet\UDB3\Role\ValueObjects\Permission;
use ValueObjects\Identity\UUID;
use ValueObjects\String\String as StringLiteral;

class Role extends EventSourcedAggregateRoot
{
    /**
     * @var UUID
     */
    private $uuid;

    /**
     * @var StringLiteral
     */
    private $name;

    /**
     * @return string
     */
    public function getAggregateRootId()
    {
        return $this->uuid;
    }

    /**
     * @param UUID $uuid
     * @param StringLiteral $name
     * @return Role
     */
    public static function createRole(
        UUID $uuid,
        StringLiteral $name
    ) {
        $role = new Role();

        $role->apply(new RoleCreated(
            $uuid,
            $name
        ));

        return $role;
    }

    /**
     * Rename the role.
     *
     * @param UUID $uuid
     * @param StringLiteral $name
     */
    public function renameRole(UUID $uuid, StringLiteral $name)
    {
        $this->apply(new RoleRenamed($uuid, $name));
    }

    /**
     * Add a permission to the role.
     *
     * @param Permission $permission
     */
    public function addPermission(Permission $permission)
    {
        $this->apply(new PermissionAdded($this->uuid, $permission));
    }

    /**
     * Remove a permission from the role.
     *
     * @param Permission $permission
     */
    public function removePermission(Permission $permission)
    {
        $this->apply(new PermissionRemoved($this->uuid, $permission));
    }
}
