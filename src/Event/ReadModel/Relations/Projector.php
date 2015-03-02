<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Event\ReadModel\Relations;

use CultuurNet\UDB3\Cdb\EventItemFactory;
use CultuurNet\UDB3\Event\EventCreated;
use CultuurNet\UDB3\Event\EventImportedFromUDB2;
use CultuurNet\UDB3\ReadModel\Udb3Projector;

class Projector extends Udb3Projector
{
    /**
     * @var RepositoryInterface
     */
    protected $repository;

    public function __construct($repository)
    {
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
        $placeId = null;
        if ($location->getCdbid()) {
            $placeId = $location->getCdbid();
        }

        $organizer = $udb2Event->getOrganiser();
        $organizerId = null;
        if ($organizer && $organizer->getCdbid()) {
            $organizerId = $organizer->getCdbid();
        }

        $this->storeRelations($eventId, $placeId, $organizerId);
    }

    protected function applyEventCreated(EventCreated $event)
    {
        $eventId = $event->getEventId();

        // Store relation if the event is connected with a place.
        $cdbid = $event->getLocation()->getCdbid();
        if (!empty($cdbid)) {
          $organizer = null;
          $this->storeRelations($eventId, $cdbid, $organizer);
        }

    }

    protected function storeRelations($eventId, $placeId, $organizerId)
    {
        $this->repository->storeRelations($eventId, $placeId, $organizerId);
    }
}
