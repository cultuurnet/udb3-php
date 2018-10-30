<?php

namespace CultuurNet\UDB3\Role\ReadModel\Search;

use Broadway\EventHandling\EventListenerInterface;
use Broadway\Domain\DomainMessage;
use CultuurNet\UDB3\EventHandling\DelegateEventHandlingToSpecificMethodTrait;
use CultuurNet\UDB3\Role\Events\ConstraintAdded;
use CultuurNet\UDB3\Role\Events\ConstraintRemoved;
use CultuurNet\UDB3\Role\Events\ConstraintUpdated;
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
    public function __construct(RepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param RoleCreated $roleCreated
     * @param DomainMessage $domainMessage
     */
    public function applyRoleCreated(
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
    public function applyRoleRenamed(
        RoleRenamed $roleRenamed,
        DomainMessage $domainMessage
    ) {
        $this->repository->updateName(
            $roleRenamed->getUuid()->toNative(),
            $roleRenamed->getName()->toNative()
        );
    }

    /**
     * @param RoleDeleted $roleDeleted
     * @param DomainMessage $domainMessage
     */
    public function applyRoleDeleted(
        RoleDeleted $roleDeleted,
        DomainMessage $domainMessage
    ) {
        $this->repository->remove($roleDeleted->getUuid()->toNative());
    }

    /**
     * @param ConstraintAdded $constraintAdded
     */
    protected function applyConstraintAdded(ConstraintAdded $constraintAdded)
    {
        $this->repository->updateConstraint(
            $constraintAdded->getUuid(),
            $constraintAdded->getQuery()
        );
    }

    /**
     * @param ConstraintUpdated $constraintUpdated
     */
    protected function applyConstraintUpdated(ConstraintUpdated $constraintUpdated)
    {
        $this->repository->updateConstraint(
            $constraintUpdated->getUuid(),
            $constraintUpdated->getQuery()
        );
    }

    /**
     * @param ConstraintRemoved $constraintRemoved
     */
    protected function applyConstraintRemoved(ConstraintRemoved $constraintRemoved)
    {
        $this->repository->updateConstraint(
            $constraintRemoved->getUuid()
        );
    }
}
