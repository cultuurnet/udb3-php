<?php

namespace CultuurNet\UDB3\Role\Services;

use Broadway\CommandHandling\CommandBusInterface;
use Broadway\Repository\RepositoryInterface;
use Broadway\UuidGenerator\UuidGeneratorInterface;
use CultuurNet\UDB3\Role\Commands\AddLabel;
use CultuurNet\UDB3\Role\Commands\AddPermission;
use CultuurNet\UDB3\Role\Commands\DeleteRole;
use CultuurNet\UDB3\Role\Commands\RemoveLabel;
use CultuurNet\UDB3\Role\Commands\RemovePermission;
use CultuurNet\UDB3\Role\Commands\RenameRole;
use CultuurNet\UDB3\Role\Commands\SetConstraint;
use CultuurNet\UDB3\Role\Role;
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
     * @var RepositoryInterface
     */
    private $writeRepository;

    /**
     * DefaultRoleEditingService constructor.
     *
     * @param CommandBusInterface $commandBus
     * @param UuidGeneratorInterface $uuidGenerator
     * @param RepositoryInterface $writeRepository
     */
    public function __construct(
        CommandBusInterface $commandBus,
        UuidGeneratorInterface $uuidGenerator,
        RepositoryInterface $writeRepository
    ) {
        $this->commandBus = $commandBus;
        $this->uuidGenerator = $uuidGenerator;
        $this->writeRepository = $writeRepository;
    }

    /**
     * @inheritdoc
     */
    public function create(StringLiteral $name)
    {
        $uuid = new UUID($this->uuidGenerator->generate());

        $role = Role::create($uuid, $name);

        $this->writeRepository->save($role);

        return $uuid->toNative();
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

    /**
     * @inheritdoc
     */
    public function setConstraint(UUID $uuid, StringLiteral $query)
    {
        $command = new SetConstraint(
            $uuid,
            $query
        );

        return $this->commandBus->dispatch($command);
    }

    /**
     * @inheritdoc
     */
    public function addLabel(UUID $uuid, UUID $labelId)
    {
        $command = new AddLabel(
            $uuid,
            $labelId
        );

        return $this->commandBus->dispatch($command);
    }

    /**
     * {@inheritdoc}
     */
    public function removeLabel(UUID $uuid, UUID $labelId)
    {
        $command = new RemoveLabel(
            $uuid,
            $labelId
        );

        return $this->commandBus->dispatch($command);
    }

    /**
     * {@inheritdoc}
     */
    public function delete(UUID $uuid)
    {
        $command = new DeleteRole(
            $uuid
        );

        return $this->commandBus->dispatch($command);
    }
}
