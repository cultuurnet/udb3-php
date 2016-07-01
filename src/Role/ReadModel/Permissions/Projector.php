<?php

namespace CultuurNet\UDB3\Role\ReadModel\Permissions;

use Broadway\Domain\DateTime;
use Broadway\Domain\DomainMessage;
use CultuurNet\UDB3\Role\Events\PermissionAdded;
use CultuurNet\UDB3\Role\Events\PermissionRemoved;
use CultuurNet\UDB3\Role\ReadModel\RoleProjector;

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
        $json->permissions = (object) [$permissionName  => $permissionValue];

        $recordedOn = $domainMessage->getRecordedOn()->toString();
        $datetime = \DateTime::createFromFormat(
            DateTime::FORMAT_STRING,
            $recordedOn
        )->format('c');

        if (empty($json->created)) {
            $json->created = $datetime;
        }
        $json->modified = $datetime;

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
        unset($json->permissions->$permissionName);

        $recordedOn = $domainMessage->getRecordedOn()->toString();
        $json->modified = \DateTime::createFromFormat(
            DateTime::FORMAT_STRING,
            $recordedOn
        )->format('c');

        $this->repository->save($document->withBody($json));
    }
}
