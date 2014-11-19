<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Event;

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

    /**
     * @param EventImportedFromUDB2 $eventImportedFromUDB2
     */
    protected function applyEventImportedFromUDB2(
        EventImportedFromUDB2 $eventImportedFromUDB2
    ) {
        // @todo put this code in a separate class
        // @todo put the SCHEME in a separate field of the event
        // so old events can still be parsed when cdbxml changes
        $udb2SimpleXml = new \SimpleXMLElement(
            $eventImportedFromUDB2->getCdbXml(),
            0,
            false,
            \CultureFeed_Cdb_Default::CDB_SCHEME_URL
        );

        $udb2Event = \CultureFeed_Cdb_Item_Event::parseFromCdbXml(
            $udb2SimpleXml
        );

        $document = $this->newDocument($eventImportedFromUDB2->getEventId());
        $eventLd = $document->getBody();

        /** @var \CultureFeed_Cdb_Data_EventDetail $detail */
        $language_fallbacks = array('nl', 'en', 'fr', 'de');
        foreach ($language_fallbacks as $language) {
            $detail = $udb2Event->getDetails()->getDetailByLanguage($language);
            if ($detail) {
                break;
            }
        }

        $pictures = $detail->getMedia()->byMediaType(
            \CultureFeed_Cdb_Data_File::MEDIA_TYPE_PHOTO
        );

        $pictures->rewind();
        $picture = count($pictures) > 0 ? $pictures->current() : null;

        $eventLd->name = $detail->getTitle();
        $eventLd->shortDescription = $detail->getShortDescription();
        $eventLd->concept = array_values($udb2Event->getKeywords());
        $eventLd->calendarSummary = $detail->getCalendarSummary();
        $eventLd->image = $picture ? $picture->getHLink() : null;
        $eventLd->location = $udb2Event->getLocation()->getLabel();

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
        $eventLd->concept[] = (string)$eventTagged->getKeyword();

        $this->repository->save($document->withBody($eventLd));
    }

    public function applyTagErased(TagErased $tagErased)
    {
        $document = $this->loadDocumentFromRepository($tagErased);

        $eventLd = $document->getBody();
        $eventLd->concept = array_filter(
            $eventLd->concept,
            function ($keyword) use ($tagErased) {
                return $keyword !== (string)$tagErased->getKeyword();
            }
        );

        $this->repository->save($document->withBody($eventLd));
    }

    /**
     * @param string $id
     * @return JsonDocument
     */
    protected function newDocument($id)
    {
        $document = new JsonDocument($id);

        $eventLd = $document->getBody();
        $eventLd->{'@id'} = $this->iriGenerator->iri($id);

        // @todo provide Event-LD context here relative to the base URI
        $eventLd->{'@context'} = '/api/1.0/event.jsonld';

        return $document->withBody($eventLd);
    }

    /**
     * @param EventEvent $event
     * @return JsonDocument
     */
    protected function loadDocumentFromRepository(EventEvent $event)
    {
        $document = $this->repository->get($event->getEventId());

        if (!$document) {
            return $this->newDocument($event->getEventId());
        }

        return $document;
    }
} 
