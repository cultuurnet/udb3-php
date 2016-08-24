<?php

namespace CultuurNet\UDB3\Role\ReadModel\Detail;

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
     */
    protected function applyRoleCreated(
        RoleCreated $roleCreated
    ) {
        $this->saveNewDocument(
            $roleCreated->getUuid()->toNative(),
            function (\stdClass $json) use ($roleCreated) {
                $json->{'uuid'} = $roleCreated->getUuid()->toNative();
                $json->name = $roleCreated->getName()->toNative();

                return $json;
            }
        );
    }

    /**
     * @param RoleRenamed $roleRenamed
     */
    protected function applyRoleRenamed(
        RoleRenamed $roleRenamed
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
     */
    protected function applyRoleDeleted(
        RoleDeleted $roleDeleted
    ) {
        $this->repository->remove($roleDeleted->getUuid());
    }

    /**
     * @param ConstraintCreated $constraintCreated
     */
    protected function applyConstraintCreated(
        ConstraintCreated $constraintCreated
    ) {
        $document = $this->loadDocumentFromRepositoryByUuid(
            $constraintCreated->getUuid()->toNative()
        );

        $json = $document->getBody();
        $json->constraint = $constraintCreated->getQuery()->toNative();

        $this->repository->save($document->withBody($json));
    }

    /**
     * @param ConstraintUpdated $constraintUpdated
     */
    protected function applyConstraintUpdated(
        ConstraintUpdated $constraintUpdated
    ) {
        $document = $this->loadDocumentFromRepositoryByUuid(
            $constraintUpdated->getUuid()->toNative()
        );

        $json = $document->getBody();
        $json->constraint = $constraintUpdated->getQuery()->toNative();

        $this->repository->save($document->withBody($json));
    }

    /**
     * @param ConstraintRemoved $constraintRemoved
     */
    protected function applyConstraintRemoved(
        ConstraintRemoved $constraintRemoved
    ) {
        $document = $this->loadDocumentFromRepositoryByUuid(
            $constraintRemoved->getUuid()->toNative()
        );

        $json = $document->getBody();
        unset($json->constraint);

        $this->repository->save($document->withBody($json));
    }
}
