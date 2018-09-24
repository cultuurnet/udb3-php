<?php

namespace CultuurNet\UDB3\MyOrganizers\ReadModel;

use Broadway\EventHandling\EventListenerInterface;
use CultuurNet\UDB3\DomainMessageAdapter as DomainMessage;
use CultuurNet\UDB3\EventHandling\DelegateEventHandlingToSpecificMethodTraitWithDomainMessageAdapter;
use CultuurNet\UDB3\Organizer\Events\OrganizerCreated;
use CultuurNet\UDB3\Organizer\Events\OrganizerCreatedWithUniqueWebsite;
use CultuurNet\UDB3\Organizer\Events\OrganizerDeleted;
use CultuurNet\UDB3\Organizer\Events\OrganizerEvent;
use CultuurNet\UDB3\Organizer\OrganizerProjectedToJSONLD;

class Projector implements EventListenerInterface
{
    use DelegateEventHandlingToSpecificMethodTraitWithDomainMessageAdapter;

    /**
     * @var RepositoryInterface
     */
    private $repository;

    public function __construct(
        RepositoryInterface $repository
    ) {
        $this->repository = $repository;
    }

    public function applyOrganizerProjectedToJSONLD(
        OrganizerProjectedToJSONLD $organizerProjectedToJSONLD,
        DomainMessage $domainMessage
    ) {

        $this->repository->setUpdateDate(
            $organizerProjectedToJSONLD->getId(),
            $domainMessage->getRecordedDateTime()
        );
    }

    public function applyOrganizerCreated(
        OrganizerCreated $organizerCreated,
        DomainMessage $domainMessage
    ) {
        $this->addToRepository(
            $organizerCreated,
            $domainMessage
        );
    }

    public function applyOrganizerCreatedWithUniqueWebsite(
        OrganizerCreatedWithUniqueWebsite $organizerCreatedWithUniqueWebsite,
        DomainMessage $domainMessage
    ) {
        $this->addToRepository(
            $organizerCreatedWithUniqueWebsite,
            $domainMessage
        );
    }

    private function addToRepository(
        OrganizerEvent $organizerEvent,
        DomainMessage $domainMessage
    ) {
        $this->repository->add(
            $organizerEvent->getOrganizerId(),
            $domainMessage->getUserId(),
            $domainMessage->getRecordedDateTime()
        );
    }

    public function applyOrganizerDeleted(
        OrganizerDeleted $organizerDeleted
    ) {
        $this->repository->delete($organizerDeleted->getOrganizerId());
    }
}
