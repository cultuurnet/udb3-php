<?php

namespace CultuurNet\UDB3\Role;

use Broadway\EventSourcing\EventSourcedAggregateRoot;
use CultuurNet\UDB3\Role\Events\ConstraintAdded;
use CultuurNet\UDB3\Role\Events\ConstraintRemoved;
use CultuurNet\UDB3\Role\Events\ConstraintUpdated;
use CultuurNet\UDB3\Role\Events\LabelAdded;
use CultuurNet\UDB3\Role\Events\LabelRemoved;
use CultuurNet\UDB3\Role\Events\PermissionAdded;
use CultuurNet\UDB3\Role\Events\PermissionRemoved;
use CultuurNet\UDB3\Role\Events\RoleCreated;
use CultuurNet\UDB3\Role\Events\RoleDeleted;
use CultuurNet\UDB3\Role\Events\RoleRenamed;
use CultuurNet\UDB3\Role\Events\UserAdded;
use CultuurNet\UDB3\Role\Events\UserRemoved;
use CultuurNet\UDB3\Role\ValueObjects\Permission;
use CultuurNet\UDB3\Role\ValueObjects\Query;
use CultuurNet\UDB3\ValueObject\SapiVersion;
use ValueObjects\Identity\UUID;
use ValueObjects\StringLiteral\StringLiteral;

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
     * @var UUID[]
     */
    private $labelIds = [];

    /**
     * @var StringLiteral[]
     */
    private $userIds = [];

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
        $sapiVersion = SapiVersion::V2();
        if (empty($this->query)) {
            if (!empty($query) && !$query->isEmpty()) {
                $this->apply(new ConstraintAdded($uuid, $sapiVersion, new Query($query)));
            }
        } else {
            if (!empty($query) && !$query->isEmpty()) {
                $this->apply(new ConstraintUpdated($uuid, $sapiVersion, new Query($query)));
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
     * @param UUID $labelId
     */
    public function addLabel(
        UUID $labelId
    ) {
        if (!in_array($labelId, $this->labelIds)) {
            $this->apply(new LabelAdded($this->uuid, $labelId));
        }
    }

    /**
     * @param \ValueObjects\Identity\UUID $labelId
     */
    public function removeLabel(
        UUID $labelId
    ) {
        if (in_array($labelId, $this->labelIds)) {
            $this->apply(new LabelRemoved($this->uuid, $labelId));
        }
    }

    /**
     * @param StringLiteral $userId
     */
    public function addUser(
        StringLiteral $userId
    ) {
        if (!in_array($userId, $this->userIds)) {
            $this->apply(new UserAdded($this->uuid, $userId));
        }
    }

    /**
     * @param StringLiteral $userId
     */
    public function removeUser(
        StringLiteral $userId
    ) {
        if (in_array($userId, $this->userIds)) {
            $this->apply(new UserRemoved($this->uuid, $userId));
        }
    }

    /**
     * Delete a role.
     *
     * @param UUID $uuid
     */
    public function delete(
        UUID $uuid
    ) {
        $this->apply(new RoleDeleted($uuid));
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
     * @param ConstraintAdded $constraintAdded
     */
    public function applyConstraintAdded(ConstraintAdded $constraintAdded)
    {
        $this->query = $constraintAdded->getQuery();
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

    /**
     * @param PermissionRemoved $permissionRemoved
     */
    public function applyPermissionRemoved(PermissionRemoved $permissionRemoved)
    {
        $permission = $permissionRemoved->getPermission();

        unset($this->permissions[$permission->getName()]);
    }

    /**
     * @param LabelAdded $labelAdded
     */
    public function applyLabelAdded(LabelAdded $labelAdded)
    {
        $labelId = $labelAdded->getLabelId();
        $this->labelIds[] = $labelId;
    }

    /**
     * @param LabelRemoved $labelRemoved
     */
    public function applyLabelRemoved(LabelRemoved $labelRemoved)
    {
        $labelId = $labelRemoved->getLabelId();
        $this->labelIds = array_diff($this->labelIds, [$labelId]);
    }

    /**
     * @param UserAdded $userAdded
     */
    public function applyUserAdded(UserAdded $userAdded)
    {
        $userId = $userAdded->getUserId();
        $this->userIds[] = $userId;
    }

    /**
     * @param UserRemoved $userRemoved
     */
    public function applyUserRemoved(UserRemoved $userRemoved)
    {
        $userId = $userRemoved->getUserId();

        if (($index = array_search($userId, $this->userIds)) !== false) {
            unset($this->userIds[$index]);
        }
    }
}
