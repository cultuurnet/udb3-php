<?php

namespace CultuurNet\UDB3\Role;

use Broadway\EventSourcing\EventSourcedAggregateRoot;
use CultuurNet\UDB3\Role\Events\ConstraintCreated;
use CultuurNet\UDB3\Role\Events\ConstraintRemoved;
use CultuurNet\UDB3\Role\Events\ConstraintUpdated;
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
     * @var StringLiteral
     */
    private $query;

    /**
     * @var Permission[]
     */
    private $permissions = [];

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
    public static function create(
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
    public function rename(
        UUID $uuid,
        StringLiteral $name
    ) {
        $this->apply(new RoleRenamed($uuid, $name));
    }

    /**
     * Set a constraint on the role.
     *
     * @param UUID $uuid
     * @param StringLiteral $query
     */
    public function setConstraint(
        UUID $uuid,
        StringLiteral $query
    ) {
        if (empty($this->query)) {
            if (!empty($query) && !$query->isEmpty()) {
                $this->apply(new ConstraintCreated($uuid, $query));
            }
        } else {
            if (!empty($query) && !$query->isEmpty()) {
                $this->apply(new ConstraintUpdated($uuid, $query));
            } else {
                $this->apply(new ConstraintRemoved($uuid));
            }
        }
    }

    /**
     * Add a permission to the role.
     *
     * @param UUID $uuid
     * @param Permission $permission
     */
    public function addPermission(
        UUID $uuid,
        Permission $permission
    ) {
        if (!in_array($permission, $this->permissions)) {
            $this->apply(new PermissionAdded($uuid, $permission));
        }
    }

    /**
     * Remove a permission from the role.
     *
     * @param UUID $uuid
     * @param Permission $permission
     */
    public function removePermission(
        UUID $uuid,
        Permission $permission
    ) {
        if (in_array($permission, $this->permissions)) {
            $this->apply(new PermissionRemoved($uuid, $permission));
        }
    }

    /**
     * @param RoleCreated $roleCreated
     */
    public function applyRoleCreated(RoleCreated $roleCreated)
    {
        $this->uuid = $roleCreated->getUuid();
        $this->name = $roleCreated->getName();
    }

    /**
     * @param RoleRenamed $roleRenamed
     */
    public function applyRoleRenamed(RoleRenamed $roleRenamed)
    {
        $this->name = $roleRenamed->getName();
    }

    /**
     * @param ConstraintCreated $constraintCreated
     */
    public function applyConstraintCreated(ConstraintCreated $constraintCreated)
    {
        $this->query = $constraintCreated->getQuery();
    }

    /**
     * @param ConstraintUpdated $constraintUpdated
     */
    public function applyConstraintUpdated(ConstraintUpdated $constraintUpdated)
    {
        $this->query = $constraintUpdated->getQuery();
    }

    /**
     * @param ConstraintRemoved $constraintRemoved
     */
    public function applyConstraintRemoved(ConstraintRemoved $constraintRemoved)
    {
        $this->query = new StringLiteral('');
    }

    /**
     * @param PermissionAdded $permissionAdded
     */
    public function applyPermissionAdded(PermissionAdded $permissionAdded)
    {
        $permission = $permissionAdded->getPermission();

        $this->permissions[$permission->getName()] = $permission;
    }

    public function applyPermissionRemoved(PermissionRemoved $permissionRemoved)
    {
        $permission = $permissionRemoved->getPermission();

        unset($this->permissions[$permission->getName()]);
    }
}
