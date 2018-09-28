<?php

namespace CultuurNet\UDB3\MyOrganizers\ReadModel;

use Broadway\Domain\DomainMessage;
use Broadway\EventHandling\EventListenerInterface;
use CultuurNet\Broadway\Domain\ReplayDetectorTrait;
use CultuurNet\UDB3\Cdb\ActorItemFactory;
use CultuurNet\UDB3\Cdb\ItemBaseAdapterFactory;
use CultuurNet\UDB3\EventHandling\DelegateEventHandlingToSpecificMethodTrait;
use CultuurNet\UDB3\Organizer\Events\OrganizerImportedFromUDB2;

class UDB2Projector implements EventListenerInterface
{
    use DelegateEventHandlingToSpecificMethodTrait;
    use ReplayDetectorTrait;

    /**
     * @var RepositoryInterface
     */
    private $repository;

    /**
     * @var ItemBaseAdapterFactory
     */
    protected $itemBaseAdapterFactory;

    /**
     * UDB2Projector constructor.
     *
     * @param RepositoryInterface $repository
     * @param ItemBaseAdapterFactory $itemBaseAdapterFactory
     */
    public function __construct(
        RepositoryInterface $repository,
        ItemBaseAdapterFactory $itemBaseAdapterFactory
    ) {
        $this->repository = $repository;
        $this->itemBaseAdapterFactory = $itemBaseAdapterFactory;
    }

    public function applyOrganizerImportedFromUDB2(
        OrganizerImportedFromUDB2 $organizerImportedFromUDB2,
        DomainMessage $domainMessage
    ) {
        $udb2Actor = ActorItemFactory::createActorFromCdbXml(
            $organizerImportedFromUDB2->getCdbXmlNamespaceUri(),
            $organizerImportedFromUDB2->getCdbXml()
        );

        $udb2ActorAdapter = $this->itemBaseAdapterFactory->create($udb2Actor);

        if ($this->isReplayed($domainMessage)) {
            $this->repository->delete($udb2Actor->getCdbId());
        }

        $this->repository->add(
            $udb2Actor->getCdbId(),
            $udb2ActorAdapter->getResolvedCreatorUserId(),
            $udb2ActorAdapter->getCreationDateTime()
        );
        
        $this->repository->setUpdateDate(
            $udb2Actor->getCdbId(),
            $udb2ActorAdapter->getLastUpdateDateTime()
        );
    }
}
