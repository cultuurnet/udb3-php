<?php

namespace CultuurNet\UDB3\Role\ReadModel\Constraints;

use Broadway\EventHandling\EventListenerInterface;
use CultuurNet\UDB3\EventHandling\DelegateEventHandlingToSpecificMethodTrait;
use CultuurNet\UDB3\Role\Events\ConstraintCreated;
use CultuurNet\UDB3\Role\Events\ConstraintRemoved;
use CultuurNet\UDB3\Role\Events\ConstraintUpdated;
use CultuurNet\UDB3\Role\Events\RoleDeleted;

class UserConstraintsProjector implements EventListenerInterface
{
    use DelegateEventHandlingToSpecificMethodTrait;

    /**
     * @var UserConstraintsWriteRepositoryInterface
     */
    private $userConstraintsWriteRepository;

    /**
     * UserConstraintsProjector constructor.
     * @param UserConstraintsWriteRepositoryInterface $userConstraintsWriteRepository
     */
    public function __construct(
        UserConstraintsWriteRepositoryInterface $userConstraintsWriteRepository
    ) {
        $this->userConstraintsWriteRepository = $userConstraintsWriteRepository;
    }

    /**
     * @param ConstraintCreated $constraintCreated
     */
    protected function applyConstraintCreated(ConstraintCreated $constraintCreated)
    {
        $this->userConstraintsWriteRepository->insertRole(
            $constraintCreated->getUuid(),
            $constraintCreated->getQuery()
        );
    }

    /**
     * @param ConstraintUpdated $constraintUpdated
     */
    protected function applyConstraintUpdated(ConstraintUpdated $constraintUpdated)
    {
        $this->userConstraintsWriteRepository->updateRole(
            $constraintUpdated->getUuid(),
            $constraintUpdated->getQuery()
        );
    }

    /**
     * @param ConstraintRemoved $constraintRemoved
     */
    protected function applyConstraintRemoved(ConstraintRemoved $constraintRemoved)
    {
        $this->userConstraintsWriteRepository->removeRole(
            $constraintRemoved->getUuid()
        );
    }

    /**
     * @param RoleDeleted $roleDeleted
     */
    protected function applyRoleDeleted(RoleDeleted $roleDeleted)
    {
        $this->userConstraintsWriteRepository->removeRole(
            $roleDeleted->getUuid()
        );
    }
}
