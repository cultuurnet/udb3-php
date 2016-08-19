<?php

namespace CultuurNet\UDB3\Role\ReadModel\Detail;

use Broadway\Domain\DateTime;
use Broadway\Domain\DomainMessage;
use CultuurNet\UDB3\Role\Commands\SetConstraint;
use CultuurNet\UDB3\Role\Events\ConstraintCreated;
use CultuurNet\UDB3\Role\Events\ConstraintRemoved;
use CultuurNet\UDB3\Role\Events\ConstraintUpdated;
use CultuurNet\UDB3\Role\Events\PermissionAdded;
use CultuurNet\UDB3\Role\Events\PermissionRemoved;
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
                $json->{'@id'} = $roleCreated->getUuid()->toNative();
                $json->name = (object) ['nl' => $roleCreated->getName()->toNative()];

                $recordedOn = $domainMessage->getRecordedOn()->toString();
                $json->created = \DateTime::createFromFormat(
                    DateTime::FORMAT_STRING,
                    $recordedOn
                )->format('c');
                $json->modified = $json->created;

                $metaData = $domainMessage->getMetadata()->serialize();
                if (isset($metaData['user_email'])) {
                    $json->creator = $metaData['user_email'];
                } elseif (isset($metaData['user_nick'])) {
                    $json->creator = $metaData['user_nick'];
                }
                $json->permissions = [];

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
        $json->name->nl = $roleRenamed->getName()->toNative();

        $recordedOn = $domainMessage->getRecordedOn()->toString();
        $json->modified = \DateTime::createFromFormat(
            DateTime::FORMAT_STRING,
            $recordedOn
        )->format('c');

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

        $recordedOn = $domainMessage->getRecordedOn()->toString();
        $json->modified = \DateTime::createFromFormat(
            DateTime::FORMAT_STRING,
            $recordedOn
        )->format('c');

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

        $recordedOn = $domainMessage->getRecordedOn()->toString();
        $json->modified = \DateTime::createFromFormat(
            DateTime::FORMAT_STRING,
            $recordedOn
        )->format('c');

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

        $recordedOn = $domainMessage->getRecordedOn()->toString();
        $json->modified = \DateTime::createFromFormat(
            DateTime::FORMAT_STRING,
            $recordedOn
        )->format('c');

        $this->repository->save($document->withBody($json));
    }

    public function applyPermissionAdded(
        PermissionAdded $permissionAdded,
        DomainMessage $domainMessage
    ) {
        $document = $this->loadDocumentFromRepositoryByUuid(
            $permissionAdded->getUuid()->toNative()
        );

        $permission = $permissionAdded->getPermission();

        $json = $document->getBody();

        $permissions = property_exists($json, 'permissions') ? $json->permissions : [];
        array_push($permissions, $permission->getName());

        $json->permissions = array_unique($permissions);

        $recordedOn = $domainMessage->getRecordedOn()->toString();
        $json->modified = \DateTime::createFromFormat(
            DateTime::FORMAT_STRING,
            $recordedOn
        )->format('c');

        $this->repository->save($document->withBody($json));
    }

    /**
     * @param PermissionRemoved $permissionRemoved
     * @param DomainMessage $domainMessage
     */
    public function applyPermissionRemoved(
        PermissionRemoved $permissionRemoved,
        DomainMessage $domainMessage
    ) {
        $document = $this->loadDocumentFromRepositoryByUuid(
            $permissionRemoved->getUuid()->toNative()
        );

        $permission = $permissionRemoved->getPermission();
        $permissionName = $permission->getName();

        $json = $document->getBody();
        $json->permissions = array_values(array_filter($json->permissions, function ($item) use ($permissionName) {
            return $item !== $permissionName;
        }));

        $recordedOn = $domainMessage->getRecordedOn()->toString();
        $json->modified = \DateTime::createFromFormat(
            DateTime::FORMAT_STRING,
            $recordedOn
        )->format('c');

        $this->repository->save($document->withBody($json));
    }
}
