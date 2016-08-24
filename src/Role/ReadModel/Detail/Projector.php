<?php

namespace CultuurNet\UDB3\Role\ReadModel\Detail;

use Broadway\Domain\DomainMessage;
use CultuurNet\UDB3\Role\Events\ConstraintCreated;
use CultuurNet\UDB3\Role\Events\ConstraintRemoved;
use CultuurNet\UDB3\Role\Events\ConstraintUpdated;
use CultuurNet\UDB3\Role\Events\RoleCreated;
use CultuurNet\UDB3\Role\Events\RoleDeleted;
use CultuurNet\UDB3\Role\Events\RoleRenamed;
use CultuurNet\UDB3\Role\ReadModel\RoleProjector;

class Projector extends RoleProjector
{
    /**
     * @param RoleCreated $roleCreated
     * @param DomainMessage $domainMessage
     */
    protected function applyRoleCreated(
        RoleCreated $roleCreated,
        DomainMessage $domainMessage
    ) {
        $this->saveNewDocument(
            $roleCreated->getUuid()->toNative(),
            function (\stdClass $json) use ($roleCreated, $domainMessage) {
                $json->{'uuid'} = $roleCreated->getUuid()->toNative();
                $json->name = $roleCreated->getName()->toNative();

                return $json;
            }
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
        $document = $this->loadDocumentFromRepositoryByUuid(
            $roleRenamed->getUuid()->toNative()
        );

        $json = $document->getBody();
        $json->name = $roleRenamed->getName()->toNative();

        $this->repository->save($document->withBody($json));
    }

    /**
     * @param RoleDeleted $roleDeleted
     * @param DomainMessage $domainMessage
     */
    protected function applyRoleDeleted(
        RoleDeleted $roleDeleted,
        DomainMessage $domainMessage
    ) {
        $this->repository->remove($roleDeleted->getUuid());
    }

    /**
     * @param \CultuurNet\UDB3\Role\Events\ConstraintCreated $constraintCreated
     * @param \Broadway\Domain\DomainMessage $domainMessage
     */
    protected function applyConstraintCreated(
        ConstraintCreated $constraintCreated,
        DomainMessage $domainMessage
    ) {
        $document = $this->loadDocumentFromRepositoryByUuid(
            $constraintCreated->getUuid()->toNative()
        );

        $json = $document->getBody();
        $json->constraint = $constraintCreated->getQuery()->toNative();

        $this->repository->save($document->withBody($json));
    }

    /**
     * @param \CultuurNet\UDB3\Role\Events\ConstraintUpdated $constraintUpdated
     * @param \Broadway\Domain\DomainMessage $domainMessage
     */
    protected function applyConstraintUpdated(
        ConstraintUpdated $constraintUpdated,
        DomainMessage $domainMessage
    ) {
        $document = $this->loadDocumentFromRepositoryByUuid(
            $constraintUpdated->getUuid()->toNative()
        );

        $json = $document->getBody();
        $json->constraint = $constraintUpdated->getQuery()->toNative();

        $this->repository->save($document->withBody($json));
    }

    /**
     * @param \CultuurNet\UDB3\Role\Events\ConstraintRemoved $constraintRemoved
     * @param \Broadway\Domain\DomainMessage $domainMessage
     */
    protected function applyConstraintRemoved(
        ConstraintRemoved $constraintRemoved,
        DomainMessage $domainMessage
    ) {
        $document = $this->loadDocumentFromRepositoryByUuid(
            $constraintRemoved->getUuid()->toNative()
        );

        $json = $document->getBody();
        unset($json->constraint);

        $this->repository->save($document->withBody($json));
    }
}
