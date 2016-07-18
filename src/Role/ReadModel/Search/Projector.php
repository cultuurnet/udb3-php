<?php

namespace CultuurNet\UDB3\Role\ReadModel\Search;

use Broadway\EventHandling\EventListenerInterface;
use Broadway\Domain\DomainMessage;
use CultuurNet\UDB3\EventHandling\DelegateEventHandlingToSpecificMethodTrait;
use CultuurNet\UDB3\Role\Events\RoleCreated;
use CultuurNet\UDB3\Role\Events\RoleRenamed;
use CultuurNet\UDB3\Role\Events\RoleDeleted;

class Projector implements EventListenerInterface
{
    use DelegateEventHandlingToSpecificMethodTrait;

    /**
     * @var RepositoryInterface
     */
    private $repository;

    /**
     * @param RepositoryInterface $repository
     */
    public function __construct(RepositoryInterface $repository) {
        $this->repository = $repository;
    }

    /**
     * @param RoleCreated $roleCreated
     * @param DomainMessage $domainMessage
     */
    protected function applyRoleCreated(
        RoleCreated $roleCreated,
        DomainMessage $domainMessage
    ) {
        $this->repository->save(
            $roleCreated->getUuid()->toNative(),
            $roleCreated->getName()->toNative()
        );
    }

    /**
     * @param RoleRenamed $roleRenamed
     * @param DomainMessage $domainMessage
     */
    protected function applyRoleRenamed(
        RoleRenamed $roleRenamed,
        DomainMessage $domainMessage
    ) {
        $this->repository->update(
            $roleRenamed->getUuid()->toNative(),
            $roleRenamed->getName()->toNative()
        );
    }

    /**
     * @param RoleDeleted $roleDeleted
     * @param DomainMessage $domainMessage
     */
    protected function applyRoleDeleted(
        RoleDeleted $roleDeleted,
        DomainMessage $domainMessage
    ) {
        $this->repository->remove($roleDeleted->getUuid()->toNative());
    }
}
