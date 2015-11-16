<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Event\ReadModel\Relations;

use Broadway\EventHandling\EventListenerInterface;
use CultuurNet\UDB3\Cdb\EventItemFactory;
use CultuurNet\UDB3\Event\EventEvent;
use CultuurNet\UDB3\Event\Events\EventCreated;
use CultuurNet\UDB3\Event\Events\EventCreatedFromCdbXml;
use CultuurNet\UDB3\Event\Events\EventDeleted;
use CultuurNet\UDB3\Event\Events\EventImportedFromUDB2;
use CultuurNet\UDB3\Event\Events\EventUpdatedFromCdbXml;
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

        $placeId = $this->getPlaceId($udb2Event);
        $organizerId = $this->getOrganizerId($udb2Event);

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

        $placeId = $this->getPlaceIdFromEventEvent($organizerUpdated);

        $this->storeRelations($organizerUpdated->getEventId(), $placeId, $organizerUpdated->getOrganizerId());
    }

    /**
     * Remove the relation.
     */
    protected function applyOrganizerDeleted(OrganizerDeleted $organizerDeleted)
    {

        $eventEntity = $this->eventService->getEvent($organizerDeleted->getEventId());
        $event = json_decode($eventEntity);

        $placeId = $this->getPlaceIdFromEventEvent($organizerDeleted);

        $this->storeRelations($organizerDeleted->getEventId(), $placeId, null);
    }

    protected function storeRelations($eventId, $placeId, $organizerId)
    {
        $this->repository->storeRelations($eventId, $placeId, $organizerId);
    }

    /**
     * @param EventCreatedFromCdbXml $eventCreatedFromCdbXml
     */
    protected function applyEventCreatedFromCdbXml(EventCreatedFromCdbXml $eventCreatedFromCdbXml)
    {
        $eventId = $eventCreatedFromCdbXml->getEventId();

        $udb2Event = EventItemFactory::createEventFromCdbXml(
            $eventCreatedFromCdbXml->getCdbXmlNamespaceUri()->toNative(),
            $eventCreatedFromCdbXml->getEventXmlString()->toEventXmlString()
        );

        $placeId = $this->getPlaceId($udb2Event);
        $organizerId = $this->getOrganizerId($udb2Event);

        $this->storeRelations($eventId, $placeId, $organizerId);
    }

    /**
    * @param EventUpdatedFromCdbXml $eventUpdatedFromCdbXml
    */
    protected function applyEventUpdatedFromCdbXml(EventUpdatedFromCdbXml $eventUpdatedFromCdbXml)
    {
        $eventId = $eventUpdatedFromCdbXml->getEventId();

        $udb2Event = EventItemFactory::createEventFromCdbXml(
            $eventUpdatedFromCdbXml->getCdbXmlNamespaceUri()->toNative(),
            $eventUpdatedFromCdbXml->getEventXmlString()->toEventXmlString()
        );

        $placeId = $this->getPlaceId($udb2Event);
        $organizerId = $this->getOrganizerId($udb2Event);

        $this->storeRelations($eventId, $placeId, $organizerId);
    }


    /**
     * @param \CultureFeed_Cdb_Item_Event $udb2Event
     * @return string
     */
    protected function getPlaceId(\CultureFeed_Cdb_Item_Event $udb2Event)
    {
        $location = $udb2Event->getLocation();
        $placeId = null;
        if ($location->getCdbid()) {
            $placeId = $location->getCdbid();
        }

        return $placeId;
    }

    /**
     * @param \CultuurNet\UDB3\Event\EventEvent $event
     * @return string|null
     */
    protected function getPlaceIdFromEventEvent(EventEvent $event)
    {
        $placeId = null;

        if (!empty($event->location)) {
            $idParts = explode('/', $event->location->{'@id'});
            $placeId = array_pop($idParts);
        }

        return $placeId;
    }

    /**
     * @param \CultureFeed_Cdb_Item_Event $udb2Event
     * @return string
     */
    protected function getOrganizerId(\CultureFeed_Cdb_Item_Event $udb2Event)
    {
        $organizer = $udb2Event->getOrganiser();
        $organizerId = null;
        if ($organizer && $organizer->getCdbid()) {
            $organizerId = $organizer->getCdbid();
        }

        return $organizerId;
    }
}
