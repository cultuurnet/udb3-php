<?php

namespace CultuurNet\UDB3\Role;

use Broadway\CommandHandling\CommandHandler as AbstractCommandHandler;
use Broadway\Repository\RepositoryInterface;
use CultuurNet\UDB3\Role\Commands\AddPermission;
use CultuurNet\UDB3\Role\Commands\CreateRole;
use CultuurNet\UDB3\Role\Commands\RemovePermission;
use CultuurNet\UDB3\Role\Commands\RenameRole;
use ValueObjects\Identity\UUID;

class CommandHandler extends AbstractCommandHandler
{
    /**
     * @var RepositoryInterface
     */
    private $repository;

    /**
     * CommandHandler constructor.
     * @param RepositoryInterface $repository
     */
    public function __construct(RepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param CreateRole $createRole
     */
    public function handleCreateRole(CreateRole $createRole)
    {
        $role = Role::create(
            $createRole->getUuid(),
            $createRole->getName()
        );

        $this->save($role);
    }

    /**
     * @param RenameRole $renameRole
     */
    public function handleRenameRole(RenameRole $renameRole)
    {
        $role = $this->load($renameRole->getUuid());
        
        $role->rename(
            $renameRole->getUuid(),
            $renameRole->getName()
        );

        $this->save($role);
    }

    /**
     * @param AddPermission $addPermission
     */
    public function handleAddPermission(AddPermission $addPermission)
    {
        $role = $this->load($addPermission->getUuid());

        $role->addPermission(
            $addPermission->getUuid(),
            $addPermission->getPermission()
        );

        $this->save($role);
    }

    /**
     * @param RemovePermission $removePermission
     */
    public function handleRemovePermission(RemovePermission $removePermission)
    {
        $role = $this->load($removePermission->getUuid());

        $role->removePermission(
            $removePermission->getUuid(),
            $removePermission->getPermission()
        );

        $this->save($role);
    }

    /**
     * @param UUID $uuid
     * @return Role
     */
    private function load(UUID $uuid)
    {
        return $this->repository->load($uuid);
    }

    /**
     * @param Role $role
     */
    private function save(Role $role)
    {
        $this->repository->save($role);
    }
}
