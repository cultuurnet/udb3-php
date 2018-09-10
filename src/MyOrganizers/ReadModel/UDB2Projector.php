<?php

namespace CultuurNet\UDB3\MyOrganizers\ReadModel;

use Broadway\EventHandling\EventListenerInterface;
use CultuurNet\UDB3\Cdb\ActorItemFactory;
use CultuurNet\UDB3\Cdb\ItemBaseAdapterFactory;
use CultuurNet\UDB3\EventHandling\DelegateEventHandlingToSpecificMethodTrait;
use CultuurNet\UDB3\Organizer\Events\OrganizerImportedFromUDB2;

class UDB2Projector implements EventListenerInterface
{
    use DelegateEventHandlingToSpecificMethodTrait;

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
        OrganizerImportedFromUDB2 $organizerImportedFromUDB2
    ) {
        $udb2Actor = ActorItemFactory::createActorFromCdbXml(
            $organizerImportedFromUDB2->getCdbXmlNamespaceUri(),
            $organizerImportedFromUDB2->getCdbXml()
        );

        $udb2ActorAdapter = $this->itemBaseAdapterFactory->create($udb2Actor);

        $this->repository->add(
            $udb2Actor->getCdbId(),
            $udb2ActorAdapter->getResolvedCreatorUserId(),
            $udb2ActorAdapter->getCreationDateTime()
        );
    }
}
