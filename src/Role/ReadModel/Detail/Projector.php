<?php

namespace CultuurNet\UDB3\Role\ReadModel\Detail;

use Broadway\Domain\DateTime;
use Broadway\Domain\DomainMessage;
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
}
