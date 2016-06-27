<?php

namespace CultuurNet\UDB3\Role;

use Broadway\CommandHandling\Testing\CommandHandlerScenarioTestCase;
use Broadway\EventHandling\EventBusInterface;
use Broadway\EventStore\EventStoreInterface;
use CultuurNet\UDB3\Role\Commands\CreateRole;
use CultuurNet\UDB3\Role\Commands\RenameRole;
use CultuurNet\UDB3\Role\Events\RoleCreated;
use CultuurNet\UDB3\Role\Events\RoleRenamed;
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
     * @var RoleCreated
     */
    private $roleCreated;

    /**
     * @var RoleRenamed
     */
    private $roleRenamed;

    public function setUp()
    {
        parent::setUp();

        $this->uuid = new UUID();
        $this->name = new StringLiteral('labelName');

        $this->roleCreated = new RoleCreated(
            $this->uuid,
            $this->name
        );
        
        $this->roleRenamed = new RoleRenamed(
            $this->uuid,
            $this->name
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
    public function it_handles_createRole()
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
    public function it_handles_renameRole()
    {
        $this->scenario
            ->withAggregateId($this->uuid)
            ->given([])
            ->when(new RenameRole(
                $this->uuid,
                $this->name
            ))
            ->then([$this->roleRenamed]);
    }
}
