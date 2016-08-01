<?php

namespace CultuurNet\UDB3\Role;

use Broadway\EventSourcing\Testing\AggregateRootScenarioTestCase;
use CultuurNet\UDB3\Role\Events\ConstraintCreated;
use CultuurNet\UDB3\Role\Events\ConstraintRemoved;
use CultuurNet\UDB3\Role\Events\ConstraintUpdated;
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
     * @var StringLiteral
     */
    private $query;

    /**
     * @var StringLiteral
     */
    private $updatedQuery;

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
     * @var ConstraintCreated
     */
    private $constraintCreated;

    /**
     * @var ConstraintUpdated
     */
    private $constraintUpdated;

    /**
     * @var ConstraintRemoved
     */
    private $constraintRemoved;

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
        $this->query = new StringLiteral('category_flandersregion_name:"Regio Aalst"');
        $this->updatedQuery = new StringLiteral('category_flandersregion_name:"Regio Brussel"');

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

        $this->constraintCreated = new ConstraintCreated(
            $this->uuid,
            $this->query
        );

        $this->constraintUpdated = new ConstraintUpdated(
            $this->uuid,
            $this->updatedQuery
        );
        
        $this->constraintRemoved = new ConstraintRemoved(
            $this->uuid
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

    /**
     * @test
     */
    public function it_can_add_a_constraint()
    {
        $uuid = $this->uuid;
        $query = $this->query;

        $this->scenario
            ->withAggregateId($this->uuid)
            ->given([$this->roleCreated])
            ->when(function (Role $role) use ($uuid, $query) {
                /** @var Role $role */
                $role->setConstraint(
                    $uuid,
                    $query
                );
            })
            ->then([$this->constraintCreated]);
    }

    /**
     * @test
     */
    public function it_can_update_an_existing_constraint()
    {
        $uuid = $this->uuid;
        $query = $this->updatedQuery;

        $this->scenario
            ->withAggregateId($this->uuid)
            ->given([$this->roleCreated, $this->constraintCreated])
            ->when(function (Role $role) use ($uuid, $query) {
                /** @var Role $role */
                $role->setConstraint(
                    $uuid,
                    $query
                );
            })
            ->then([$this->constraintUpdated]);
    }

    /**
     * @test
     */
    public function it_can_remove_a_constraint()
    {
        $uuid = $this->uuid;
        $query = new StringLiteral('');

        $this->scenario
            ->withAggregateId($this->uuid)
            ->given([$this->roleCreated, $this->constraintCreated])
            ->when(function (Role $role) use ($uuid, $query) {
                /** @var Role $role */
                $role->setConstraint(
                    $uuid,
                    $query
                );
            })
            ->then([$this->constraintRemoved]);
    }

    /**
     * @test
     */
    public function it_does_not_remove_a_constraint_when_there_is_none()
    {
        $uuid = $this->uuid;
        $query = new StringLiteral('');

        $this->scenario
            ->withAggregateId($this->uuid)
            ->given([$this->roleCreated])
            ->when(function (Role $role) use ($uuid, $query) {
                /** @var Role $role */
                $role->setConstraint(
                    $uuid,
                    $query
                );
            })
            ->then([]);
    }
}
