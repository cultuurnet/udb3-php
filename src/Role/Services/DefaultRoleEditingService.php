<?php

namespace CultuurNet\UDB3\Role\Services;

use Broadway\CommandHandling\CommandBusInterface;
use Broadway\UuidGenerator\UuidGeneratorInterface;
use CultuurNet\UDB3\Role\Commands\AddPermission;
use CultuurNet\UDB3\Role\Commands\CreateRole;
use CultuurNet\UDB3\Role\Commands\RemovePermission;
use CultuurNet\UDB3\Role\Commands\RenameRole;
use CultuurNet\UDB3\Role\Commands\SetConstraint;
use CultuurNet\UDB3\Role\ValueObjects\Permission;
use ValueObjects\Identity\UUID;
use ValueObjects\String\String as StringLiteral;

class DefaultRoleEditingService implements RoleEditingServiceInterface
{
    /**
     * @var CommandBusInterface
     */
    private $commandBus;

    /**
     * @var UuidGeneratorInterface
     */
    private $uuidGenerator;

    /**
     * DefaultRoleEditingService constructor.
     *
     * @param CommandBusInterface $commandBus
     * @param UuidGeneratorInterface $uuidGenerator
     */
    public function __construct(
        CommandBusInterface $commandBus,
        UuidGeneratorInterface $uuidGenerator
    ) {
        $this->commandBus = $commandBus;
        $this->uuidGenerator = $uuidGenerator;
    }

    /**
     * @inheritdoc
     */
    public function create(StringLiteral $name)
    {
        $uuid = new UUID($this->uuidGenerator->generate());

        $command = new CreateRole(
            $uuid,
            $name
        );

        return $this->commandBus->dispatch($command);
    }

    /**
     * @inheritdoc
     */
    public function rename(UUID $uuid, StringLiteral $name)
    {
        $command = new RenameRole(
            $uuid,
            $name
        );

        return $this->commandBus->dispatch($command);
    }

    /**
     * @inheritdoc
     */
    public function addPermission(UUID $uuid, Permission $permission)
    {
        $command = new AddPermission(
            $uuid,
            $permission
        );

        return $this->commandBus->dispatch($command);
    }

    /**
     * @inheritdoc
     */
    public function removePermission(UUID $uuid, Permission $permission)
    {
        $command = new RemovePermission(
            $uuid,
            $permission
        );

        return $this->commandBus->dispatch($command);
    }

    public function setConstraint(UUID $uuid, StringLiteral $query)
    {
        $command = new SetConstraint(
            $uuid,
            $query
        );

        return $this->commandBus->dispatch($command);
    }
}
