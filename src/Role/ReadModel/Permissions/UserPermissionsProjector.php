<?php

namespace CultuurNet\UDB3\Role\ReadModel\Permissions;

use Broadway\Domain\DomainMessage;
use Broadway\EventHandling\EventListenerInterface;
use CultuurNet\UDB3\EventHandling\DelegateEventHandlingToSpecificMethodTrait;
use CultuurNet\UDB3\Role\Events\PermissionAdded;
use CultuurNet\UDB3\Role\Events\PermissionRemoved;
use CultuurNet\UDB3\Role\Events\RoleCreated;
use CultuurNet\UDB3\Role\Events\RoleDeleted;
use CultuurNet\UDB3\Role\Events\UserAdded;
use CultuurNet\UDB3\Role\Events\UserRemoved;

class UserPermissionsProjector implements EventListenerInterface
{
    use DelegateEventHandlingToSpecificMethodTrait;

    /**
     * @var UserPermissionsWriteRepositoryInterface
     */
    private $repository;

    /**
     * UserPermissionsProjector constructor.
     * @param UserPermissionsWriteRepositoryInterface $repository
     */
    public function __construct(UserPermissionsWriteRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }


    /**
     * @param RoleDeleted $roleDeleted
     * @param DomainMessage $domainMessage
     */
    public function applyRoleDeleted(RoleDeleted $roleDeleted, DomainMessage $domainMessage)
    {
        $this->repository->removePermissionsByRole($roleDeleted->getUuid());
    }

    /**
     * @param UserAdded $userAdded
     * @param DomainMessage $domainMessage
     */
    public function applyUserAdded(UserAdded $userAdded, DomainMessage $domainMessage)
    {
        $this->repository->addPermissionsByUserRole($userAdded->getUserId(), $userAdded->getUuid());
    }

    /**
     * @param UserRemoved $userRemoved
     * @param DomainMessage $domainMessage
     */
    public function applyUserRemoved(UserRemoved $userRemoved, DomainMessage $domainMessage)
    {
        $this->repository->removePermissionsByUserRole($userRemoved->getUserId(), $userRemoved->getUuid());
    }

    /**
     * @param PermissionAdded $permissionAdded
     * @param DomainMessage $domainMessage
     */
    public function applyPermissionAdded(PermissionAdded $permissionAdded, DomainMessage $domainMessage)
    {
        $this->repository->addRolePermission($permissionAdded->getUuid(), $permissionAdded->getPermission());
    }

    /**
     * @param PermissionRemoved $permissionRemoved
     * @param DomainMessage $domainMessage
     */
    public function applyPermissionRemoved(PermissionRemoved $permissionRemoved, DomainMessage $domainMessage)
    {
        $this->repository->removeRolePermission($permissionRemoved->getUuid(), $permissionRemoved->getPermission());
    }
}
