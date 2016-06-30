<?php

namespace CultuurNet\UDB3\Role\ReadModel\Detail;

use Broadway\Domain\DateTime;
use Broadway\Domain\DomainMessage;
use Broadway\EventHandling\EventListenerInterface;
use CultuurNet\UDB3\Event\ReadModel\DocumentRepositoryInterface;
use CultuurNet\UDB3\EventHandling\DelegateEventHandlingToSpecificMethodTrait;
use CultuurNet\UDB3\ReadModel\JsonDocument;
use CultuurNet\UDB3\Role\Events\RoleCreated;
use CultuurNet\UDB3\Role\Events\RoleRenamed;

class Projector implements EventListenerInterface
{
    use DelegateEventHandlingToSpecificMethodTrait;

    /**
     * @var DocumentRepositoryInterface
     */
    protected $repository;

    /**
     * Projector constructor.
     * @param DocumentRepositoryInterface $repository
     */
    public function __construct(
        DocumentRepositoryInterface $repository
    ) {
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
        $this->saveNewDocument(
            $roleCreated->getUuid()->toNative(),
            function (\stdClass $json) use ($roleCreated, $domainMessage) {
                $json->{'@id'} = $roleCreated->getUuid()->toNative();
                $json->name['nl'] = $roleCreated->getName()->toNative();

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
            $roleRenamed->getUuid()
        );

        $json = $document->getBody();
        $json->name['nl'] = $roleRenamed->getName()->fromNative();

        $recordedOn = $domainMessage->getRecordedOn()->toString();
        $json->modified = \DateTime::createFromFormat(
            DateTime::FORMAT_STRING,
            $recordedOn
        )->format('c');

        $this->repository->save($document->withBody($json));
    }

    /**
     * @param string $uuid
     * @param callable $fn
     */
    protected function saveNewDocument($uuid, callable $fn)
    {
        $document = $this
            ->newDocument($uuid)
            ->apply($fn);

        $this->repository->save($document);
    }

    /**
     * @param string $uuid
     * @return JsonDocument
     */
    protected function loadDocumentFromRepositoryByUuid($uuid)
    {
        $document = $this->repository->get($uuid);

        if (!$document) {
            return $this->newDocument($uuid);
        }

        return $document;
    }

    /**
     * @param string $uuid
     * @return JsonDocument
     */
    protected function newDocument($uuid)
    {
        $document = new JsonDocument($uuid);

        $json = $document->getBody();
        $json->{'@id'} = $uuid;

        return $document->withBody($json);
    }
}
