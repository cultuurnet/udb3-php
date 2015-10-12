<?php
/**
 * @file
 */

namespace CultuurNet\UDB3;

use Broadway\Repository\AggregateNotFoundException;
use Broadway\Repository\RepositoryInterface;
use CultuurNet\UDB3\Event\ReadModel\DocumentRepositoryInterface;
use CultuurNet\UDB3\Event\ReadModel\Relations\RepositoryInterface as RelationsRepository;
use CultuurNet\UDB3\ReadModel\JsonDocument;

class LocalEventService implements EventServiceInterface
{
    /**
     * @var DocumentRepositoryInterface
     */
    protected $documentRepository;

    /**
     * @var RepositoryInterface
     */
    protected $eventRepository;

    /**
     * @var Event\ReadModel\Relations\RepositoryInterface
     */
    protected $eventRelationsRepository;

    public function __construct(
        DocumentRepositoryInterface $documentRepository,
        RepositoryInterface $eventRepository,
        RelationsRepository $eventRelationsRepository
    ) {
        $this->documentRepository = $documentRepository;
        $this->eventRepository = $eventRepository;
        $this->eventRelationsRepository = $eventRelationsRepository;
    }

    /**
     * Get a single event by its id.
     *
     * @param string $id
     *   A string uniquely identifying an event.
     *
     * @return array
     *   An event array.
     *
     * @throws EventNotFoundException if an event can not be found for the given id
     */
    public function getEvent($id)
    {
        /** @var JsonDocument $document */
        $document = $this->documentRepository->get($id);

        if ($document) {
            return $document->getRawBody();
        }

        // @todo subsequent load and add are necessary for UDB2 repository
        // decorator, but this particular code should be moved over to an
        // EventService decorator
        try {
            $this->eventRepository->load($id);
        } catch (AggregateNotFoundException $e) {
            throw new EventNotFoundException(
                sprintf('Event with id: %s not found.', $id)
            );
        }

        /** @var JsonDocument $document */
        $document = $this->documentRepository->get($id);

        if ($document) {
            return $document->getRawBody();
        }
    }

    /**
     * @param string $organizerId
     * @return string[]
     */
    public function eventsOrganizedByOrganizer($organizerId)
    {
        return $this->eventRelationsRepository->getEventsOrganizedByOrganizer($organizerId);
    }

    /**
     * @param string $placeId
     * @return string[]
     */
    public function eventsLocatedAtPlace($placeId)
    {
        return $this->eventRelationsRepository->getEventsLocatedAtPlace($placeId);
    }
}
