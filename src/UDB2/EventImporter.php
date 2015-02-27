<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\UDB2;

use Broadway\Domain\DomainMessageInterface;
use Broadway\EventHandling\EventListenerInterface;
use Broadway\Repository\AggregateNotFoundException;
use Broadway\Repository\RepositoryInterface;
use CultuurNet\UDB2DomainEvents\EventCreated;
use CultuurNet\UDB2DomainEvents\EventUpdated;
use CultuurNet\UDB3\Cdb\EventItemFactory;
use CultuurNet\UDB3\EntityNotFoundException;
use CultuurNet\UDB3\Event\Event;
use CultuurNet\UDB3\OrganizerService;
use CultuurNet\UDB3\PlaceService;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerTrait;

/**
 * Listens for update/create events coming from UDB2 and applies the
 * resulting cdbXml to the UDB3 events.
 */
class EventImporter implements EventListenerInterface, EventImporterInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @var EventCdbXmlServiceInterface
     */
    private $cdbXmlService;

    /**
     * @var RepositoryInterface
     */
    protected $repository;

    /**
     * @var OrganizerService
     */
    protected $organizerService;

    /**
     * @var PlaceService
     */
    protected $placeService;

    /**
     * @param EventCdbXmlServiceInterface $cdbXmlService
     */
    public function __construct(
        EventCdbXmlServiceInterface $cdbXmlService,
        RepositoryInterface $repository,
        PlaceService $placeService,
        OrganizerService $organizerService
    ) {
        $this->cdbXmlService = $cdbXmlService;
        $this->repository = $repository;
        $this->placeService = $placeService;
        $this->organizerService = $organizerService;
    }

    /**
     * @param DomainMessageInterface $domainMessage
     */
    public function handle(DomainMessageInterface $domainMessage)
    {
        $event = $domainMessage->getPayload();

        if ($event instanceof EventCreated) {
            $this->handleEventCreated($event);
        } elseif ($event instanceof EventUpdated) {
            $this->handleEventUpdated($event);
        }
    }

    private function handleEventCreated(EventCreated $eventCreated)
    {
        // @todo Should we add additional layer to check for author and timestamp?
        $this->createEventFromUDB2($eventCreated->getEventId());
    }

    private function handleEventUpdated(EventUpdated $eventUpdated)
    {
        // @todo Should we add additional layer to check for author and timestamp?
        $this->updateEventFromUDB2($eventUpdated->getEventId());
    }

    public function updateEventFromUDB2($eventId)
    {
        try {
            $event = $this->loadEvent($eventId);
        } catch (AggregateNotFoundException $e) {
            return $this->createEventFromUDB2($eventId);
        }

        $eventXml = $this->getCdbXmlOfEvent($eventId);

        $this->importDependencies($eventXml);

        $event->updateWithCdbXml(
            $eventXml,
            \CultureFeed_Cdb_Default::CDB_SCHEME_URL
        );

        $this->repository->add($event);

        return $event;
    }

    /**
     * @param string $eventId
     * @return Event
     */
    private function loadEvent($eventId)
    {
        return $this->repository->load($eventId);
    }

    /**
     * @param string $eventId
     * @return string
     */
    private function getCdbXmlOfEvent($eventId)
    {
        return $this->cdbXmlService->getCdbXmlOfEvent($eventId);
    }

    public function createEventFromUDB2($eventId)
    {
        $eventXml = $this->getCdbXmlOfEvent($eventId);

        $this->importDependencies($eventXml);

        $event = Event::importFromUDB2(
            $eventId,
            $eventXml,
            \CultureFeed_Cdb_Default::CDB_SCHEME_URL
        );

        $this->repository->add($event);

        return $event;
    }

    /**
     * @param string $eventXml
     * @throws EntityNotFoundException
     * @throws \Exception
     */
    private function importDependencies($eventXml)
    {
        $udb2Event = EventItemFactory::createEventFromCdbXml(
            \CultureFeed_Cdb_Default::CDB_SCHEME_URL,
            $eventXml
        );

        try {
            $location = $udb2Event->getLocation();
            if ($location && $location->getCdbid()) {
                // Loading the place will implicitly import it, or throw an error
                // if the place is not known.
                $this->placeService->getEntity($location->getCdbid());
            }
        } catch (EntityNotFoundException $e) {
            if ($this->logger) {
                $this->logger->error(
                    "Unable to retrieve location with ID {$location->getCdbid(
                    )}, of event {$udb2Event->getCdbId()}."
                );
            } else {
                throw $e;
            }
        }

        try {
            $organizer = $udb2Event->getOrganiser();
            if ($organizer && $organizer->getCdbid()) {
                // Loading the organizer will implicitly import it, or throw an error
                // if the organizer is not known.
                $this->organizerService->getEntity($organizer->getCdbid());
            }
        } catch (EntityNotFoundException $e) {
            if ($this->logger) {
                $this->logger->error(
                    "Unable to retrieve organizer with ID {$organizer->getCdbid(
                    )}, of event {$udb2Event->getCdbId()}."
                );
            } else {
                throw $e;
            }
        }
    }
}
