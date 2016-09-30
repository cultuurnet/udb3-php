<?php

namespace CultuurNet\UDB3\Organizer\ReadModel\Search;

use Broadway\Domain\DomainMessage;
use Broadway\EventHandling\EventListenerInterface;
use CultuurNet\UDB3\EventHandling\DelegateEventHandlingToSpecificMethodTrait;
use CultuurNet\UDB3\Organizer\Events\OrganizerCreatedWithUniqueWebsite;
use CultuurNet\UDB3\Organizer\Events\OrganizerDeleted;

class Projector implements EventListenerInterface
{
    use DelegateEventHandlingToSpecificMethodTrait;

    /**
     * @var RepositoryInterface
     */
    private $repository;

    /**
     * @param RepositoryInterface $repository
     */
    public function __construct(RepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param OrganizerCreatedWithUniqueWebsite $organizerCreated
     * @param DomainMessage $domainMessage
     */
    public function applyOrganizerCreatedWithUniqueWebsite(
        OrganizerCreatedWithUniqueWebsite $organizerCreated,
        DomainMessage $domainMessage
    ) {
        $this->repository->save(
            $organizerCreated->getOrganizerId(),
            $organizerCreated->getTitle()->toNative(),
            $organizerCreated->getWebsite()->__toString()
        );
    }

    /**
     * @param OrganizerDeleted $organizerDeleted
     * @param DomainMessage $domainMessage
     */
    public function applyRoleDeleted(
        OrganizerDeleted $organizerDeleted,
        DomainMessage $domainMessage
    ) {
        $this->repository->remove($organizerDeleted->getOrganizerId());
    }

//    /**
//     * @param WebsiteUpdated $websiteUpdated
//     */
//    protected function applyWebsitetUpdated(WebsiteUpdated $websiteUpdated)
//    {
//        $this->repository->updateWebsite(
//            $websiteUpdated->getOrganizerId(),
//            $websiteUpdated->getWebsite()->__toString()
//        );
//    }
}
