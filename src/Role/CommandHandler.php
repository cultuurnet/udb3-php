<?php

namespace CultuurNet\UDB3\Role;

use Broadway\CommandHandling\CommandHandler as AbstractCommandHandler;
use Broadway\Repository\RepositoryInterface;
use CultuurNet\UDB3\Role\Commands\AddPermission;
use CultuurNet\UDB3\Role\Commands\AddUser;
use CultuurNet\UDB3\Role\Commands\CreateRole;
use CultuurNet\UDB3\Role\Commands\DeleteRole;
use CultuurNet\UDB3\Role\Commands\RemovePermission;
use CultuurNet\UDB3\Role\Commands\RemoveUser;
use CultuurNet\UDB3\Role\Commands\RenameRole;
use CultuurNet\UDB3\Role\Commands\SetConstraint;
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
     * @param SetConstraint $setConstraint
     */
    public function handleSetConstraint(SetConstraint $setConstraint)
    {
        $role = $this->load($setConstraint->getUuid());

        $role->setConstraint(
            $setConstraint->getUuid(),
            $setConstraint->getQuery()
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
     * @param AddUser $addUser
     */
    public function handleAddUser(AddUser $addUser)
    {
        $role = $this->load($addUser->getUuid());

        $role->addUser(
            $addUser->getUuid(),
            $addUser->getUserId()
        );

        $this->save($role);
    }

    /**
     * @param RemoveUser $removeUser
     */
    public function handleRemoveUser(RemoveUser $removeUser)
    {
        $role = $this->load($removeUser->getUuid());

        $role->removeUser(
            $removeUser->getUuid(),
            $removeUser->getUserId()
        );

        $this->save($role);
    }

    /**
     * @param DeleteRole $deleteRole
     */
    public function handleDeleteRole(DeleteRole $deleteRole)
    {
        $role = $this->load($deleteRole->getUuid());

        //@TODO Check linked users and labels once added.

        $role->delete($deleteRole->getUuid());

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
