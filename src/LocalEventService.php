<?php
/**
 * @file
 */

namespace CultuurNet\UDB3;


use Broadway\EventSourcing\EventSourcingRepository;
use Broadway\Repository\RepositoryInterface;
use CultuurNet\UDB3\Event\ReadModel\DocumentRepositoryInterface;
use CultuurNet\UDB3\Event\ReadModel\JsonDocument;
use CultuurNet\UDB3\Iri\IriGeneratorInterface;

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
     * @var IriGeneratorInterface
     */
    protected $iriGenerator;

    public function __construct(
        DocumentRepositoryInterface $documentRepository,
        RepositoryInterface $eventRepository,
        IriGeneratorInterface $iriGenerator
    )
    {
        $this->documentRepository = $documentRepository;
        $this->eventRepository = $eventRepository;
        $this->iriGenerator = $iriGenerator;
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
        $event = $this->eventRepository->load($id);

        if (!$event) {
            return NULL;
        }

        $this->eventRepository->add($event);

        /** @var JsonDocument $document */
        $document = $this->documentRepository->get($id);

        return $document->getRawBody();
    }
} 
