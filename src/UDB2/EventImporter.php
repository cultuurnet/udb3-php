<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\UDB2;

use Broadway\Domain\DomainMessage;
use Broadway\EventHandling\EventListenerInterface;
use Broadway\Repository\AggregateNotFoundException;
use Broadway\Repository\RepositoryInterface;
use CultuurNet\UDB2DomainEvents\EventCreated;
use CultuurNet\UDB2DomainEvents\EventUpdated;
use CultuurNet\UDB3\Cdb\EventItemFactory;
use CultuurNet\UDB3\EntityNotFoundException;
use CultuurNet\UDB3\Event\Event;
use CultuurNet\UDB3\EventHandling\DelegateEventHandlingToSpecificMethodTrait;
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
    use DelegateEventHandlingToSpecificMethodTrait;

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
     * @param EventCreated $eventCreated
     */
    private function applyEventCreated(EventCreated $eventCreated)
    {
        // @todo Should we add additional layer to check for author and timestamp?
        $this->createEventFromUDB2($eventCreated->getEventId());
    }

    /**
     * @param EventUpdated $eventUpdated
     */
    private function applyEventUpdated(EventUpdated $eventUpdated)
    {
        // @todo Should we add additional layer to check for author and timestamp?
        $this->updateEventFromUDB2($eventUpdated->getEventId());
    }

    /**
     * @inheritdoc
     */
    public function updateEventFromUDB2($eventId)
    {
        return $this->update($eventId);
    }

    /**
     * @param string $eventId
     * @param bool $fallbackToCreate
     * @return Event
     * @throws EntityNotFoundException
     */
    private function update($eventId, $fallbackToCreate = false)
    {
        try {
            $event = $this->loadEvent($eventId);
        } catch (AggregateNotFoundException $e) {
            if ($fallbackToCreate) {
                if ($this->logger) {
                    $this->logger->notice(
                        "Could not update event because it does not exist yet on UDB3, will attempt to create the event instead",
                        [
                            'eventId' => $eventId
                        ]
                    );
                }

                return $this->create($eventId, false);
            } else {
                if ($this->logger) {
                    $this->logger->notice(
                        "Could not update event because it does not exist yet on UDB3",
                        [
                            'eventId' => $eventId
                        ]
                    );
                }

                return;
            }
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

    /**
     * @param string $eventId
     * @param bool $fallbackToUpdate
     * @return null|Event
     */
    private function create($eventId, $fallbackToUpdate = true)
    {
        $eventXml = $this->getCdbXmlOfEvent($eventId);

        $this->importDependencies($eventXml);

        try {
            $event = Event::importFromUDB2(
                $eventId,
                $eventXml,
                \CultureFeed_Cdb_Default::CDB_SCHEME_URL
            );

            $this->repository->add($event);
        } catch (\Exception $e) {
            if ($fallbackToUpdate) {
                if ($this->logger) {
                    $this->logger->notice(
                        "Event creation in UDB3 failed with an exception, will attempt to update the event instead",
                        [
                            'exception' => $e,
                            'eventId' => $eventId
                        ]
                    );
                }
                // @todo Differentiate between event exists locally already
                // (same event arriving twice, event created on UDB3 first)
                // and a real error while saving.
                return $this->update($eventId, false);
            } else {
                if ($this->logger) {
                    $this->logger->notice(
                        "Event creation in UDB3 failed with an exception",
                        [
                            'exception' => $e,
                            'eventId' => $eventId
                        ]
                    );
                }
                return;
            }
        }

        return $event;
    }

    /**
     * @inheritdoc
     */
    public function createEventFromUDB2($eventId)
    {
        return $this->create($eventId);
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
