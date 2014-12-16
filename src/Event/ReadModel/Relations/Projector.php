<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Event\ReadModel\Relations;


use CultuurNet\UDB3\Cdb\EventItemFactory;
use CultuurNet\UDB3\Event\EventImportedFromUDB2;

class Projector extends \Broadway\ReadModel\Projector
{
    /**
     * @var RepositoryInterface
     */
    protected $repository;

    public function __construct($repository) {
        $this->repository = $repository;
    }

    protected function applyEventImportedFromUDB2(EventImportedFromUDB2 $event)
    {
        $eventId = $event->getEventId();

        $udb2Event = EventItemFactory::createEventFromCdbXml(
            $event->getCdbXmlNamespaceUri(),
            $event->getCdbXml()
        );

        $location = $udb2Event->getLocation();
        $placeId = NULL;
        if ($location->getCdbid()) {
            $placeId = $location->getCdbid();
        }

        $organizer = $udb2Event->getOrganiser();
        $organizerId = NULL;
        if ($organizer && $organizer->getCdbid()) {
            $organizerId = $organizer->getCdbid();
        }

        $this->storeRelations($eventId, $placeId, $organizerId);
    }

    protected function storeRelations($eventId, $placeId, $organizerId) {
        $this->repository->storeRelations($eventId, $placeId, $organizerId);
    }
}
