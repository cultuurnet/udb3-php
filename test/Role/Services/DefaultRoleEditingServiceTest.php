<?php

namespace CultuurNet\UDB3\Role\Services;

use Broadway\CommandHandling\CommandBusInterface;
use Broadway\EventHandling\SimpleEventBus;
use Broadway\EventStore\InMemoryEventStore;
use Broadway\EventStore\TraceableEventStore;
use Broadway\Repository\RepositoryInterface;
use Broadway\UuidGenerator\UuidGeneratorInterface;
use CultuurNet\UDB3\Role\Commands\AddPermission;
use CultuurNet\UDB3\Role\Commands\CreateRole;
use CultuurNet\UDB3\Role\Commands\DeleteRole;
use CultuurNet\UDB3\Role\Commands\RemovePermission;
use CultuurNet\UDB3\Role\Commands\RenameRole;
use CultuurNet\UDB3\Role\Commands\SetConstraint;
use CultuurNet\UDB3\Role\Events\RoleCreated;
use CultuurNet\UDB3\Role\Role;
use CultuurNet\UDB3\Role\RoleRepository;
use CultuurNet\UDB3\Role\ValueObjects\Permission;
use ValueObjects\Identity\UUID;
use ValueObjects\String\String as StringLiteral;

class DefaultRoleEditingServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CommandBusInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $commandBus;

    /**
     * @var UuidGeneratorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $uuidGenerator;

    /**
     * @var TraceableEventStore
     */
    protected $eventStore;

    /**
     * @var RepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $writeRepository;

    /**
     * @var UUID
     */
    private $uuid;

    /**
     * @var CreateRole
     */
    private $createRole;

    /**
     * @var RenameRole
     */
    private $renameRole;

    /**
     * @var SetConstraint
     */
    private $setConstraint;

    /**
     * @var AddPermission
     */
    private $addPermission;

    /**
     * @var RemovePermission
     */
    private $removePermission;

    /**
     * @var DeleteRole
     */
    private $deleteRole;

    /**
     * @var DefaultRoleEditingService
     */
    private $roleEditingService;

    /**
     * @var string
     */
    private $expectedCommandId;

    public function setUp()
    {
        $this->uuid = new UUID('9196cb78-4381-11e6-beb8-9e71128cae77');

        $this->commandBus = $this->getMock(CommandBusInterface::class);
        $this->uuidGenerator = $this->getMock(UuidGeneratorInterface::class);

        $this->eventStore = new TraceableEventStore(new InMemoryEventStore());

        $this->writeRepository = new RoleRepository(
            $this->eventStore,
            new SimpleEventBus
        );

        $this->createRole = new CreateRole(
            $this->uuid,
            new StringLiteral('roleName')
        );

        $this->renameRole = new RenameRole(
            $this->uuid,
            new StringLiteral('new roleName')
        );

        $this->setConstraint = new SetConstraint(
            $this->uuid,
            new StringLiteral('category_flandersregion_name:"Regio Brussel"')
        );

        $this->addPermission = new AddPermission(
            $this->uuid,
            Permission::AANBOD_INVOEREN()
        );
        
        $this->removePermission = new RemovePermission(
            $this->uuid,
            Permission::AANBOD_INVOEREN()
        );

        $this->deleteRole = new DeleteRole(
            $this->uuid
        );

        $this->uuidGenerator->method('generate')
            ->willReturn('9196cb78-4381-11e6-beb8-9e71128cae77');

        $this->roleEditingService = new DefaultRoleEditingService(
            $this->commandBus,
            $this->uuidGenerator,
            $this->writeRepository
        );

        $this->expectedCommandId = '123456789';
    }

    /**
     * @test
     */
    public function it_can_create_a_role()
    {
        $this->eventStore->trace();
        
        $roleId = $this->roleEditingService->create(
            new StringLiteral('roleName')
        );

        $expectedUuid = $this->uuid;

        $this->assertEquals(
            [
                new RoleCreated(
                    $this->uuid,
                    new StringLiteral('roleName')
                )
            ],
            $this->eventStore->getEvents()
        );

        $this->assertEquals($expectedUuid, $roleId);
    }

    /**
     * @test
     */
    public function it_can_rename_a_role()
    {
        $this->commandBus->expects($this->once())
            ->method('dispatch')
            ->with($this->renameRole)
            ->willReturn($this->expectedCommandId);

        $commandId = $this->roleEditingService->rename(
            $this->uuid,
            new StringLiteral('new roleName')
        );

        $this->assertEquals($this->expectedCommandId, $commandId);
    }

    /**
     * @test
     */
    public function it_can_set_a_constraint()
    {
        $this->commandBus->expects($this->once())
            ->method('dispatch')
            ->with($this->setConstraint)
            ->willReturn($this->expectedCommandId);

        $commandId = $this->roleEditingService->setConstraint(
            $this->uuid,
            new StringLiteral('category_flandersregion_name:"Regio Brussel"')
        );

        $this->assertEquals($this->expectedCommandId, $commandId);
    }

    /**
     * @test
     */
    public function it_can_add_a_permission()
    {
        $this->commandBus->expects($this->once())
            ->method('dispatch')
            ->with($this->addPermission)
            ->willReturn($this->expectedCommandId);

        $commandId = $this->roleEditingService->addPermission(
            $this->uuid,
            Permission::AANBOD_INVOEREN()
        );

        $this->assertEquals($this->expectedCommandId, $commandId);
    }

    /**
     * @test
     */
    public function it_can_remove_a_permission()
    {
        $this->commandBus->expects($this->once())
            ->method('dispatch')
            ->with($this->removePermission)
            ->willReturn($this->expectedCommandId);

        $commandId = $this->roleEditingService->removePermission(
            $this->uuid,
            Permission::AANBOD_INVOEREN()
        );

        $this->assertEquals($this->expectedCommandId, $commandId);
    }

    /**
     * @test
     */
    public function it_can_delete_a_role()
    {
        $this->commandBus->expects($this->once())
            ->method('dispatch')
            ->with($this->deleteRole)
            ->willReturn($this->expectedCommandId);

        $commandId = $this->roleEditingService->delete(
            $this->uuid
        );

        $this->assertEquals($this->expectedCommandId, $commandId);
    }
}