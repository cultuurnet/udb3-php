<?php

namespace CultuurNet\UDB3\MyOrganizers\ReadModel;

use Broadway\Domain\DomainMessage;
use Broadway\EventHandling\EventListenerInterface;
use CultuurNet\UDB3\Cdb\ActorItemFactory;
use CultuurNet\UDB3\Cdb\CreatedByToUserIdResolverInterface;
use CultuurNet\UDB3\EventHandling\DelegateEventHandlingToSpecificMethodTrait;
use CultuurNet\UDB3\Organizer\Events\OrganizerCreated;
use CultuurNet\UDB3\Organizer\Events\OrganizerCreatedWithUniqueWebsite;
use CultuurNet\UDB3\Organizer\Events\OrganizerDeleted;
use CultuurNet\UDB3\Organizer\Events\OrganizerImportedFromUDB2;
use CultuurNet\UDB3\Organizer\OrganizerProjectedToJSONLD;
use DateTime;
use DateTimeZone;
use ValueObjects\StringLiteral\StringLiteral;

class Projector implements EventListenerInterface
{
    use DelegateEventHandlingToSpecificMethodTrait;

    /**
     * @var RepositoryInterface
     */
    private $repository;

    /**
     * @var CreatedByToUserIdResolverInterface
     */
    private $userIdResolver;

    public function __construct(
        RepositoryInterface $repository,
        CreatedByToUserIdResolverInterface $userIdentityResolver
    ) {
        $this->repository = $repository;
        $this->userIdResolver = $userIdentityResolver;
    }

    public function applyOrganizerProjectedToJSONLD(
        OrganizerProjectedToJSONLD $organizerProjectedToJSONLD,
        DomainMessage $domainMessage
    ) {
        $this->repository->setUpdateDate(
            $organizerProjectedToJSONLD->getId(),
            $this->getRecordedDate($domainMessage)
        );
    }

    public function applyOrganizerImportedFromUDB2(
        OrganizerImportedFromUDB2 $organizerImportedFromUDB2
    ) {
        $udb2Actor = ActorItemFactory::createActorFromCdbXml(
            $organizerImportedFromUDB2->getCdbXmlNamespaceUri(),
            $organizerImportedFromUDB2->getCdbXml()
        );

        $this->repository->add(
            $udb2Actor->getCdbId(),
            $this->resolveUserId($udb2Actor),
            $this->dateTimeFromUDB2DateString(
                $udb2Actor->getCreationDate()
            )
        );
    }

    public function applyOrganizerCreated(
        OrganizerCreated $organizerCreated,
        DomainMessage $domainMessage
    ) {
        $this->repository->add(
            $organizerCreated->getOrganizerId(),
            $this->getUserId($domainMessage),
            $this->getRecordedDate($domainMessage)
        );
    }

    public function applyOrganizerCreatedWithUniqueWebsite(
        OrganizerCreatedWithUniqueWebsite $organizerCreatedWithUniqueWebsite,
        DomainMessage $domainMessage
    ) {
        $this->repository->add(
            $organizerCreatedWithUniqueWebsite->getOrganizerId(),
            $this->getUserId($domainMessage),
            $this->getRecordedDate($domainMessage)
        );
    }

    public function applyOrganizerDeleted(
        OrganizerDeleted $organizerDeleted
    ) {
        $this->repository->delete($organizerDeleted->getOrganizerId());
    }

    /**
     * @param DomainMessage $domainMessage
     * @return DateTime
     */
    private function getRecordedDate(DomainMessage $domainMessage)
    {
        return new DateTime($domainMessage->getRecordedOn()->toString());
    }

    /**
     * @param DomainMessage $domainMessage
     * @return string
     */
    private function getUserId(DomainMessage $domainMessage)
    {
        $metaData = $domainMessage->getMetadata()->serialize();
        return isset($metaData['user_id']) ? $metaData['user_id'] : '';
    }

    /**
     * @param \CultureFeed_Cdb_Item_Base $udb2Item
     *
     * @return null|string
     */
    private function resolveUserId(\CultureFeed_Cdb_Item_Base $udb2Item)
    {
        $createdByIdentifier = $udb2Item->getCreatedBy();
        if ($createdByIdentifier) {
            $userId = $this->userIdResolver->resolveCreatedByToUserId(
                new StringLiteral($createdByIdentifier)
            );
        }

        return isset($userId) ? (string)$userId : '';
    }

    /**
     * @param string $dateString
     *  A UDB2 formatted date string
     *
     * @return DateTime
     */
    protected function dateTimeFromUDB2DateString(string $dateString)
    {
        return DateTime::createFromFormat(
            'Y-m-d?H:i:s',
            $dateString,
            new DateTimeZone('Europe/Brussels')
        );
    }
}
