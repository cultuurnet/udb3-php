<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Event;

use Broadway\Domain\DomainMessageInterface;
use Broadway\ReadModel\Projector;
use CultuurNet\UDB3\Event\ReadModel\JsonDocument;
use CultuurNet\UDB3\Event\ReadModel\DocumentRepositoryInterface;
use CultuurNet\UDB3\Iri\IriGeneratorInterface;

class EventLDProjector extends Projector
{
    /**
     * @var DocumentRepositoryInterface
     */
    protected $repository;

    /**
     * @var IriGeneratorInterface
     */
    protected $iriGenerator;

    /**
     * @param DocumentRepositoryInterface $repository
     * @param IriGeneratorInterface $iriGenerator
     */
    public function __construct(
        DocumentRepositoryInterface $repository,
        IriGeneratorInterface $iriGenerator
    ) {
        $this->repository = $repository;
        $this->iriGenerator = $iriGenerator;
    }

    public function handle(DomainMessageInterface $domainMessage)
    {
        return parent::handle(
            $domainMessage
        );
    }


    /**
     * @param EventImportedFromUDB2 $eventImportedFromUDB2
     */
    protected function applyEventImportedFromUDB(
        EventImportedFromUDB2 $eventImportedFromUDB2
    ) {
        $eventLd = new \stdClass();


        // @todo set @context
        // @todo load event from cdbxml and set properties on eventLd object
        //$eventLdModel->name =
        //$eventLdModel->calendarSummary =
        //$eventLdModel->concept[] =

        $eventLdModel = new JsonDocument(
            $eventImportedFromUDB2->getEventId()
        );

        $this->repository->save($eventLdModel->withBody($eventLd));
    }

    /**
     * @param EventCreated $eventCreated
     */
    protected function applyEventCreated(EventCreated $eventCreated)
    {
        // @todo This just creates an empty event. Should we do anything here?
    }

    /**
     * @param EventWasTagged $eventTagged
     */
    protected function applyEventWasTagged(EventWasTagged $eventTagged)
    {
        $document = $this->loadDocumentFromRepository($eventTagged);

        $eventLd = $document->getBody();
        $eventLd->concept[] = $eventTagged->getKeyword();

        $this->repository->save($document->withBody($eventLd));
    }

    /**
     * @param EventEvent $event
     * @return JsonDocument
     */
    protected function loadDocumentFromRepository(EventEvent $event) {
        $document = $this->repository->get($event->getEventId());

        if (!$document) {
            $document = new JsonDocument($event->getEventId());
            $eventLd = $document->getBody();
            $eventLd->{'@id'} = $this->iriGenerator->iri($event->getEventId());
            $document = $document->withBody($eventLd);
        }

        return $document;
    }
} 
