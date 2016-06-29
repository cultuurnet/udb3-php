<?php

namespace CultuurNet\UDB3\Role;

use Broadway\CommandHandling\Testing\CommandHandlerScenarioTestCase;
use Broadway\EventHandling\EventBusInterface;
use Broadway\EventStore\EventStoreInterface;
use CultuurNet\UDB3\Role\Commands\AddPermission;
use CultuurNet\UDB3\Role\Commands\CreateRole;
use CultuurNet\UDB3\Role\Commands\RemovePermission;
use CultuurNet\UDB3\Role\Commands\RenameRole;
use CultuurNet\UDB3\Role\Events\PermissionAdded;
use CultuurNet\UDB3\Role\Events\PermissionRemoved;
use CultuurNet\UDB3\Role\Events\RoleCreated;
use CultuurNet\UDB3\Role\Events\RoleRenamed;
use CultuurNet\UDB3\Role\ValueObjects\Permission;
use ValueObjects\Identity\UUID;
use ValueObjects\String\String as StringLiteral;

class CommandHandlerTest extends CommandHandlerScenarioTestCase
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

    public function setUp()
    {
        parent::setUp();

        $this->uuid = new UUID();
        $this->name = new StringLiteral('labelName');
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
    }

    /**
     * @inheritdoc
     */
    protected function createCommandHandler(
        EventStoreInterface $eventStore,
        EventBusInterface $eventBus
    ) {
        return new CommandHandler(new RoleRepository(
            $eventStore,
            $eventBus
        ));
    }

    /**
     * @test
     */
    public function it_handles_create()
    {
        $this->scenario
            ->withAggregateId($this->uuid)
            ->given([])
            ->when(new CreateRole(
                $this->uuid,
                $this->name
            ))
            ->then([$this->roleCreated]);
    }

    /**
     * @test
     */
    public function it_handles_rename()
    {
        $this->scenario
            ->withAggregateId($this->uuid)
            ->given([$this->roleCreated])
            ->when(new RenameRole(
                $this->uuid,
                $this->name
            ))
            ->then([$this->roleRenamed]);
    }

    /**
     * @test
     */
    public function it_handles_addPermission()
    {
        $this->scenario
            ->withAggregateId($this->uuid)
            ->given([$this->roleCreated])
            ->when(new AddPermission(
                $this->uuid,
                $this->permission
            ))
            ->then([$this->permissionAdded]);
    }

    /**
     * @test
     */
    public function it_handles_removePermission()
    {
        $this->scenario
            ->withAggregateId($this->uuid)
            ->given([$this->roleCreated, new PermissionAdded($this->uuid, $this->permission)])
            ->when(new RemovePermission(
                $this->uuid,
                $this->permission
            ))
            ->then([$this->permissionRemoved]);
    }
}
