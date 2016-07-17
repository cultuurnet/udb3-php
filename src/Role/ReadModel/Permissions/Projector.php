<?php

namespace CultuurNet\UDB3\Role\ReadModel\Permissions;

use Broadway\Domain\DateTime;
use Broadway\Domain\DomainMessage;
use CultuurNet\UDB3\Role\Events\PermissionAdded;
use CultuurNet\UDB3\Role\Events\PermissionRemoved;
use CultuurNet\UDB3\Role\Events\RoleCreated;
use CultuurNet\UDB3\Role\Events\RoleDeleted;
use CultuurNet\UDB3\Role\ReadModel\RoleProjector;
use CultuurNet\UDB3\Role\ValueObjects\Permission;

class Projector extends RoleProjector
{
    /**
     * @param PermissionAdded $permissionAdded
     * @param DomainMessage $domainMessage
     */
    public function applyPermissionAdded(
        PermissionAdded $permissionAdded,
        DomainMessage $domainMessage
    ) {
        $document = $this->loadDocumentFromRepositoryByUuid(
            $permissionAdded->getUuid()->toNative()
        );

        $permission = $permissionAdded->getPermission();
        $permissionName = $permission->getName();
        $permissionValue = $permission->getValue();

        $json = $document->getBody();
        if (!is_array($json->permissions)) {
            $json->permissions = array();
        }
        $json->permissions[] = (object) [$permissionName  => $permissionValue];

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
        if (!is_array($json->permissions)) {
            $json->permissions = array();
        }
        $json->permissions = array_values(array_filter($json->permissions, function($item) use ($permissionName) {
            return key($item) !== $permissionName;
        }));

        $recordedOn = $domainMessage->getRecordedOn()->toString();
        $json->modified = \DateTime::createFromFormat(
            DateTime::FORMAT_STRING,
            $recordedOn
        )->format('c');

        $this->repository->save($document->withBody($json));
    }

    /**
     * @param RoleCreated $roleCreated
     * @param DomainMessage $domainMessage
     */
    public function applyRoleCreated(
        RoleCreated $roleCreated,
        DomainMessage $domainMessage
    ) {
        $this->saveNewDocument(
            $roleCreated->getUuid()->toNative(),
            function (\stdClass $json) use ($roleCreated, $domainMessage) {
                $json->{'@id'} = $roleCreated->getUuid()->toNative();
                $json->permissions = (object) [];

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

                return $json;
            }
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
        $this->repository->remove($roleDeleted->getUuid());
    }
}
