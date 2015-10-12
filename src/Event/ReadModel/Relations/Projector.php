<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Event\ReadModel\Relations;

use Broadway\EventHandling\EventListenerInterface;
use CultuurNet\UDB3\Cdb\EventItemFactory;
use CultuurNet\UDB3\Event\Events\EventCreated;
use CultuurNet\UDB3\Event\Events\EventDeleted;
use CultuurNet\UDB3\Event\Events\EventImportedFromUDB2;
use CultuurNet\UDB3\Event\Events\OrganizerDeleted;
use CultuurNet\UDB3\Event\Events\OrganizerUpdated;
use CultuurNet\UDB3\EventHandling\DelegateEventHandlingToSpecificMethodTrait;
use CultuurNet\UDB3\EventServiceInterface;

class Projector implements EventListenerInterface
{
    use DelegateEventHandlingToSpecificMethodTrait;

    /**
     * @var RepositoryInterface
     */
    protected $repository;

    /**
     * @var EventServiceInterface
     */
    protected $eventService;

    public function __construct($repository, EventServiceInterface $eventService)
    {
        $this->repository = $repository;
        $this->eventService = $eventService;
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

    /**
     * Delete the relations.
     * @param EventDeleted $event
     */
    protected function applyEventDeleted(EventDeleted $event)
    {
        $eventId = $event->getEventId();
        $this->repository->removeRelations($eventId);

    }

    /**
     * Store the relation when the organizer was changed
     */
    protected function applyOrganizerUpdated(OrganizerUpdated $organizerUpdated)
    {
        $eventEntity = $this->eventService->getEvent($organizerUpdated->getEventId());
        $event = json_decode($eventEntity);

        $placeId = !empty($event->location) ? $event->location->{'@id'}: null;

        $this->storeRelations($organizerUpdated->getEventId(), $placeId, $organizerUpdated->getOrganizerId());
    }

    /**
     * Remove the relation.
     */
    protected function applyOrganizerDeleted(OrganizerDeleted $organizerDeleted)
    {

        $eventEntity = $this->eventService->getEvent($organizerDeleted->getEventId());
        $event = json_decode($eventEntity);

        $placeId = null;
        if (!empty($event->location)) {
            $idParts = explode('/', $event->location->{'@id'});
            $placeId = array_pop($idParts);
        }

        $this->storeRelations($organizerDeleted->getEventId(), $placeId, null);
    }

    protected function storeRelations($eventId, $placeId, $organizerId)
    {
        $this->repository->storeRelations($eventId, $placeId, $organizerId);
    }
}
