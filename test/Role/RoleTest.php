<?php

namespace CultuurNet\UDB3\Role;

use Broadway\EventSourcing\Testing\AggregateRootScenarioTestCase;
use CultuurNet\UDB3\Role\Events\PermissionAdded;
use CultuurNet\UDB3\Role\Events\PermissionRemoved;
use CultuurNet\UDB3\Role\Events\RoleCreated;
use CultuurNet\UDB3\Role\Events\RoleRenamed;
use CultuurNet\UDB3\Role\ValueObjects\Permission;
use ValueObjects\Identity\UUID;
use ValueObjects\String\String as stringLiteral;

class RoleTest extends AggregateRootScenarioTestCase
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
     * @var Permission
     */
    private $permission;

    /**
     * @var RoleCreated
     */
    private $roleCreated;

    /**
     * @var RoleRenamed
     */
    private $roleRenamed;

    /**
     * @var PermissionAdded
     */
    private $permissionAdded;

    /**
     * @var PermissionRemoved
     */
    private $permissionRemoved;

    /**
     * @var Role
     */
    private $role;

    public function setUp()
    {
        parent::setUp();

        $this->uuid = new UUID();
        $this->name = new StringLiteral('roleName');
        $this->permission = Permission::AANBOD_INVOEREN();

        $this->roleCreated = new RoleCreated(
            $this->uuid,
            $this->name
        );

        $this->roleRenamed = new RoleRenamed(
            $this->uuid,
            $this->name
        );

        $this->permissionAdded = new PermissionAdded(
            $this->uuid,
            $this->permission
        );

        $this->permissionRemoved = new PermissionRemoved(
            $this->uuid,
            $this->permission
        );

        $this->role = new Role();
    }

    /**
     * Returns a string representing the aggregate root
     *
     * @return string AggregateRoot
     */
    protected function getAggregateRootClass()
    {
        return Role::class;
    }

    /**
     * @test
     */
    public function it_can_create_a_new_role()
    {
        $this->scenario
            ->when(function () {
                return Role::create(
                    $this->uuid,
                    $this->name
                );
            })
            ->then([$this->roleCreated]);
    }

    /**
     * @test
     */
    public function it_can_rename_a_role()
    {
        $uuid = $this->uuid;
        $name = $this->name;

        $this->scenario
            ->withAggregateId($this->uuid)
            ->given([$this->roleCreated])
            ->when(function ($role) use ($uuid, $name) {
                /** @var Role $role */
                $role->rename(
                    $uuid,
                    $name
                );
            })
            ->then([new RoleRenamed($this->uuid, $this->name)]);
    }

    /**
     * @test
     */
    public function it_can_add_a_permission()
    {
        $uuid = $this->uuid;
        $permission = $this->permission;

        $this->scenario
            ->withAggregateId($this->uuid)
            ->given([$this->roleCreated])
            ->when(function (Role $role) use ($uuid, $permission) {
                /** @var Role $role */
                $role->addPermission(
                    $uuid,
                    $permission
                );
            })
            ->then([$this->permissionAdded]);
    }

    /**
     * @test
     */
    public function it_cannot_add_a_permission_that_has_already_been_added()
    {
        $uuid = $this->uuid;
        $permission = $this->permission;

        $this->scenario
            ->withAggregateId($this->uuid)
            ->given([$this->roleCreated, new PermissionAdded($this->uuid, Permission::AANBOD_INVOEREN())])
            ->when(function (Role $role) use ($uuid, $permission) {
                /** @var Role $role */
                $role->addPermission(
                    $uuid,
                    $permission
                );
            })
            ->then([]);
    }

    /**
     * @test
     */
    public function it_can_remove_a_permission()
    {
        $uuid = $this->uuid;
        $permission = $this->permission;

        $this->scenario
            ->withAggregateId($this->uuid)
            ->given([$this->roleCreated, $this->permissionAdded])
            ->when(function (Role $role) use ($uuid, $permission) {
                /** @var Role $role */
                $role->removePermission(
                    $uuid,
                    $permission
                );
            })
            ->then([$this->permissionRemoved]);
    }

    /**
     * @test
     */
    public function it_cannot_remove_a_permission_that_does_not_exist_on_the_role()
    {
        $uuid = $this->uuid;
        $permission = $this->permission;

        $this->scenario
            ->withAggregateId($this->uuid)
            ->given([$this->roleCreated])
            ->when(function (Role $role) use ($uuid, $permission) {
                /** @var Role $role */
                $role->removePermission(
                    $uuid,
                    $permission
                );
            })
            ->then([]);
    }
}
