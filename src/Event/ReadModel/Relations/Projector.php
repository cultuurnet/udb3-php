<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Event\ReadModel\Relations;

use Broadway\EventHandling\EventListenerInterface;
use CultuurNet\UDB3\Cdb\CdbId\EventCdbIdExtractorInterface;
use CultuurNet\UDB3\Cdb\EventItemFactory;
use CultuurNet\UDB3\Event\Events\EventCreated;
use CultuurNet\UDB3\Event\Events\EventDeleted;
use CultuurNet\UDB3\Event\Events\EventImportedFromUDB2;
use CultuurNet\UDB3\Event\Events\EventUpdatedFromUDB2;
use CultuurNet\UDB3\Event\Events\MajorInfoUpdated;
use CultuurNet\UDB3\Event\Events\OrganizerDeleted;
use CultuurNet\UDB3\Event\Events\OrganizerUpdated;
use CultuurNet\UDB3\EventHandling\DelegateEventHandlingToSpecificMethodTrait;

class Projector implements EventListenerInterface
{
    use DelegateEventHandlingToSpecificMethodTrait;

    /**
     * @var RepositoryInterface
     */
    protected $repository;

    /**
     * @var EventCdbIdExtractorInterface
     */
    protected $cdbIdExtractor;

    /**
     * @param RepositoryInterface $repository
     * @param EventCdbIdExtractorInterface $cdbIdExtractor
     */
    public function __construct(
        RepositoryInterface $repository,
        EventCdbIdExtractorInterface $cdbIdExtractor
    ) {
        $this->repository = $repository;
        $this->cdbIdExtractor = $cdbIdExtractor;
    }

    /**
     * @param EventImportedFromUDB2 $event
     */
    protected function applyEventImportedFromUDB2(EventImportedFromUDB2 $event)
    {
        $this->applyEventDataFromUDB2($event);
    }

    /**
     * @param EventUpdatedFromUDB2 $event
     */
    protected function applyEventUpdatedFromUDB2(EventUpdatedFromUDB2 $event)
    {
        $this->applyEventDataFromUDB2($event);
    }

    /**
     * @param EventImportedFromUDB2|EventUpdatedFromUDB2 $event
     */
    protected function applyEventDataFromUDB2($event)
    {
        $eventId = $event->getEventId();

        $udb2Event = EventItemFactory::createEventFromCdbXml(
            $event->getCdbXmlNamespaceUri(),
            $event->getCdbXml()
        );

        $placeId = $this->cdbIdExtractor->getRelatedPlaceCdbId($udb2Event);
        $organizerId = $this->cdbIdExtractor->getRelatedOrganizerCdbId($udb2Event);

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

    protected function applyMajorInfoUpdated(MajorInfoUpdated $majorInfoUpdated)
    {
        $eventId = $majorInfoUpdated->getItemId();
        $cdbid = $majorInfoUpdated->getLocation()->getCdbid();
        $this->repository->storeRelation($eventId, 'place', $cdbid);
    }

    /**
     * Delete the relations.
     * @param EventDeleted $event
     */
    protected function applyEventDeleted(EventDeleted $event)
    {
        $eventId = $event->getItemId();
        $this->repository->removeRelations($eventId);

    }

    /**
     * Store the relation when the organizer was changed
     */
    protected function applyOrganizerUpdated(OrganizerUpdated $organizerUpdated)
    {
        $this->repository->storeOrganizer($organizerUpdated->getItemId(), $organizerUpdated->getOrganizerId());
    }

    /**
     * Remove the relation.
     */
    protected function applyOrganizerDeleted(OrganizerDeleted $organizerDeleted)
    {
        $this->repository->storeOrganizer($organizerDeleted->getItemId(), null);
    }

    protected function storeRelations($eventId, $placeId, $organizerId)
    {
        $this->repository->storeRelations($eventId, $placeId, $organizerId);
    }
}
