<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Event;

use Broadway\ReadModel\Projector;
use CultuurNet\UDB3\Cdb\EventItemFactory;
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
        $udb2Event = EventItemFactory::createEventFromCdbXml(
            $eventImportedFromUDB2->getCdbXmlNamespaceUri(),
            $eventImportedFromUDB2->getCdbXml()
        );

        $document = $this->newDocument($eventImportedFromUDB2->getEventId());
        $eventLd = $document->getBody();

        $translatedProperties = [
          "name" => "getTitle",
          "shortDescription" => "getShortDescription"
        ];

        $languages = array('nl', 'en', 'fr', 'de');
        // Init detail to null and cast eventLD to array to add properties
        /** @var \CultureFeed_Cdb_Data_EventDetail $detail */
        $detail = NULL;
        $eventLd = (array)$eventLd;
        foreach ($languages as $language) {

            // The first language detail found will be used as the default
            if(!$detail) {
                $detail = $udb2Event->getDetails()->getDetailByLanguage($language);
            }

            // Set the current detail to the active language so we can check for translated properties
            $languageDetail = $udb2Event->getDetails()->getDetailByLanguage($language);

            // if details are found for the language, continue
            if($languageDetail) {
                // add translated properties to the eventLd
                foreach ($translatedProperties as $property => $getterName) {
                    $propertyValue = call_user_func(array($languageDetail, $getterName));

                    if($propertyValue) {
                        $eventLd[$property][$language] = $propertyValue;
                    }
                }
            }
        }
        //cast EventLD back to an object to before adding default properties
        $eventLd = (object)$eventLd;


        $pictures = $detail->getMedia()->byMediaType(
            \CultureFeed_Cdb_Data_File::MEDIA_TYPE_PHOTO
        );

        $pictures->rewind();
        $picture = count($pictures) > 0 ? $pictures->current() : null;

        // commented out properties should be set with the foreach that iterates all the languages
        //$eventLd->name = $detail->getTitle();
        //$eventLd->shortDescription = $detail->getShortDescription();
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
        $eventLd->concept[] = $eventTagged->getKeyword();

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
    protected function loadDocumentFromRepository(EventEvent $event) {
        $document = $this->repository->get($event->getEventId());

        if (!$document) {
            return $this->newDocument($event->getEventId());
        }

        return $document;
    }
} 
