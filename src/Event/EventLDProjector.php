<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Event;

use Broadway\Domain\DateTime;
use Broadway\Domain\DomainMessageInterface;
use CultureFeed_Cdb_Data_Calendar_PeriodList;
use CultureFeed_Cdb_Data_Calendar_Permanent;
use CultureFeed_Cdb_Data_Calendar_TimestampList;
use CultureFeed_Cdb_Data_EventDetail;
use CultureFeed_Cdb_Data_File;
use CultureFeed_Cdb_Data_Language;
use CultureFeed_Cdb_Data_Performer;
use CultureFeed_Cdb_Data_Phone;
use CultuurNet\UDB3\Cdb\EventItemFactory;
use CultuurNet\UDB3\CulturefeedSlugger;
use CultuurNet\UDB3\EntityNotFoundException;
use CultuurNet\UDB3\Event\ReadModel\DocumentRepositoryInterface;

use CultuurNet\UDB3\Event\ReadModel\JsonDocument;
use CultuurNet\UDB3\StringFilter\StringFilterInterface;
use CultuurNet\UDB3\EventServiceInterface;
use CultuurNet\UDB3\Iri\IriGeneratorInterface;
use CultuurNet\UDB3\OrganizerService;
use CultuurNet\UDB3\Place\DescriptionUpdated;
use CultuurNet\UDB3\Place\PlaceProjectedToJSONLD;
use CultuurNet\UDB3\Place\TypicalAgeRangeUpdated;
use CultuurNet\UDB3\PlaceService;
use CultuurNet\UDB3\ReadModel\Udb3Projector;
use CultuurNet\UDB3\SluggerInterface;
use CultuurNet\UDB3\Calendar;
use DateTimeZone;
use stdClass;
use CultuurNet\UDB3\Event\ReadModel\JSONLD\CdbXMLImporter;
use CultuurNet\UDB3\Event\ReadModel\JSONLD\OrganizerServiceInterface;
use CultuurNet\UDB3\Event\ReadModel\JSONLD\PlaceServiceInterface;

class EventLDProjector extends Udb3Projector implements PlaceServiceInterface, OrganizerServiceInterface
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
     * @var OrganizerService
     */
    protected $organizerService;

    /**
     * @var PlaceService
     */
    protected $placeService;

    /**
     * @var EventServiceInterface
     */
    protected $eventService;

    /**
     * @var SluggerInterface
     */
    protected $slugger;

    /**
     * @var CdbXMLImporter
     */
    protected $cdbXMLImporter;

    /**
     * @param DocumentRepositoryInterface $repository
     * @param IriGeneratorInterface $iriGenerator
     * @param EventServiceInterface $eventService
     * @param PlaceService $placeService
     * @param OrganizerService $organiserService
     */
    public function __construct(
        DocumentRepositoryInterface $repository,
        IriGeneratorInterface $iriGenerator,
        EventServiceInterface $eventService,
        PlaceService $placeService,
        OrganizerService $organiserService
    ) {
        $this->repository = $repository;
        $this->iriGenerator = $iriGenerator;
        $this->organizerService = $organiserService;
        $this->placeService = $placeService;
        $this->eventService = $eventService;

        $this->slugger = new CulturefeedSlugger();
        $this->cdbXMLImporter = new CdbXMLImporter();
    }

    protected function applyOrganizerProjectedToJSONLD()
    {
        // @todo get events linked to this organizer, and update their JSON-LD
        // representation
    }

    protected function applyPlaceProjectedToJSONLD(
        PlaceProjectedToJSONLD $placeProjectedToJSONLD
    ) {
        $eventsLocatedAtPlace = $this->eventsLocatedAtPlace(
            $placeProjectedToJSONLD->getId()
        );

        $placeJSONLD = $this->placeService->getEntity(
            $placeProjectedToJSONLD->getId()
        );

        foreach ($eventsLocatedAtPlace as $eventId) {
            $document = $this->loadDocumentFromRepositoryByEventId(
                $eventId
            );
            $eventLD = $document->getBody();
            $eventLD->place = json_decode($placeJSONLD);
        }
    }

    /**
     * @param string $organizerId
     * @return string[]
     */
    protected function eventsOrganizedByOrganizer($organizerId)
    {
        return $this->eventService->eventsOrganizedByOrganizer(
            $organizerId
        );
    }

    /**
     * @param string $placeId
     * @return string[]
     */
    protected function eventsLocatedAtPlace($placeId)
    {
        return $this->eventService->eventsLocatedAtPlace(
            $placeId
        );
    }

    /**
     * @param EventImportedFromUDB2 $eventImportedFromUDB2
     */
    public function applyEventImportedFromUDB2(
        EventImportedFromUDB2 $eventImportedFromUDB2
    ) {
        $udb2Event = EventItemFactory::createEventFromCdbXml(
            $eventImportedFromUDB2->getCdbXmlNamespaceUri(),
            $eventImportedFromUDB2->getCdbXml()
        );

        $document = $this->newDocument($eventImportedFromUDB2->getEventId());
        $eventLd = $document->getBody();

        $eventLd = $this->cdbXMLImporter->documentWithCdbXML(
            $eventLd,
            $udb2Event,
            $this,
            $this,
            $this->slugger
        );

        $this->repository->save($document->withBody($eventLd));
    }

    /**
     * @param EventCreated $eventCreated
     */
    protected function applyEventCreated(EventCreated $eventCreated, DomainMessageInterface $domainMessage)
    {
        $document = $this->newDocument($eventCreated->getEventId());

        $jsonLD = $document->getBody();

        $jsonLD->{'@id'} = $this->iriGenerator->iri(
            $eventCreated->getEventId()
        );
        $jsonLD->name['nl'] = $eventCreated->getTitle();
        $jsonLD->location = array(
          '@type' => 'Place',
        ) + (array)$this->placeJSONLD($eventCreated->getLocation());

        $calendar = $eventCreated->getCalendar();
        $startDate = $calendar->getStartDate();
        $endDate = $calendar->getEndDate();
        
        // All calendar types allow startDate (and endDate).
        // One timestamp - full day.
        // One timestamp - start hour.
        // One timestamp - start and end hour.
        $jsonLD->startDate = $startDate;
        if (!empty($endDate)) {
          $jsonLD->endDate = $endDate;
        }
        
        $jsonLD->subEvent = array();
        foreach ($calendar->getTimestamps() as $timestamp) {

          $startDate = $timestamp->getDate();
          if ($timestamp->showStartHour()) {
             $startDate .= $timestamp->getTimestart();
          }
          $endDate = $timestamp->getDate();
          if ($timestamp->showEndHour()) {
             $endDate .= $timestamp->getTimeend();
          }

          $jsonLD->subEvent[] = array(
            '@type' => 'Event',
            'startDate' => $startDate,
            'endDate' => $endDate,
          );
        }
         
        // Period.
        // Period with openingtimes.
        // Permanent - "altijd open".
        // Permanent - with openingtimes.
        $jsonLD->openingHours = array();
        foreach ($calendar->getOpeningHours() as $openingHour) {
          $schedule = array('dayOfWeek' => $openingHour->daysOfWeek);
          if (!empty($openingHour->opens)) {
            $schedule['opens'] = $openingHour->opens;
          }
          if (!empty($openingHour->closes)) {
            $schedule['closes'] = $openingHour->closes;
          }
          $jsonLD->openingHours[] = $schedule;

        }

        // Same as.
        $jsonLD->sameAs = $this->generateSameAs(
            $eventCreated->getEventId(),
            reset($jsonLD->name)
        );

        $eventType = $eventCreated->getEventType();
        $jsonLD->terms = array(
            array(
                'label' => $eventType->getLabel(),
                'domain' => $eventType->getDomain(),
                'id' => $eventType->getId()
            )
        );

        $theme = $eventCreated->getTheme();
        $jsonLD->terms[] = [
             'label' => $theme->getLabel(),
             'domain' => $theme->getDomain(),
             'id' => $theme->getId()
        ];

        $recordedOn = $domainMessage->getRecordedOn()->toString();
        $jsonLD->created = \DateTime::createFromFormat(
            DateTime::FORMAT_STRING,
            $recordedOn
        )->format('c');

        $metaData = $domainMessage->getMetadata()->serialize();
        if (isset($metaData['user_id']) && isset($metaData['user_nick'])) {
            $jsonLD->creator = "{$metaData['user_id']} ({$metaData['user_nick']})";
        }

        $this->repository->save($document->withBody($jsonLD));
    }

    public function placeJSONLD($placeId)
    {
        try {
            $placeJSONLD = $this->placeService->getEntity(
                $placeId
            );

            return json_decode($placeJSONLD);
        } catch (EntityNotFoundException $e) {
            // In case the place can not be found at the moment, just add its ID
            return array(
                '@id' => $this->placeService->iri($placeId)
            );
        }
    }

    public function organizerJSONLD($organizerId)
    {
        try {
            $organizerJSONLD = $this->organizerService->getEntity(
                $organizerId
            );

            return json_decode($organizerJSONLD);
        } catch (EntityNotFoundException $e) {
            // In case the place can not be found at the moment, just add its ID
            return array(
                '@id' => $this->organizerService->iri($organizerId)
            );
        }
    }

    /**
     * @param EventWasTagged $eventTagged
     */
    protected function applyEventWasTagged(EventWasTagged $eventTagged)
    {
        $document = $this->loadDocumentFromRepository($eventTagged);

        $eventLd = $document->getBody();
        // TODO: Check if the event is already tagged with the keyword?
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
        // Ensure array keys start with 0 so json_encode() does encode it
        // as an array and not as an object.
        $eventLd->concept = array_values($eventLd->concept);

        $this->repository->save($document->withBody($eventLd));
    }

    protected function applyTitleTranslated(TitleTranslated $titleTranslated)
    {
        $document = $this->loadDocumentFromRepository($titleTranslated);

        $eventLd = $document->getBody();
        $eventLd->name->{$titleTranslated->getLanguage()->getCode(
        )} = $titleTranslated->getTitle();

        $this->repository->save($document->withBody($eventLd));
    }

    protected function applyDescriptionTranslated(
        DescriptionTranslated $descriptionTranslated
    ) {
        $document = $this->loadDocumentFromRepository($descriptionTranslated);

        $eventLd = $document->getBody();
        $eventLd->description->{$descriptionTranslated->getLanguage()->getCode(
        )} = $descriptionTranslated->getDescription();

        $this->repository->save($document->withBody($eventLd));
    }

    /**
     * Apply the description updated event to the event repository.
     * @param DescriptionUpdated $descriptionUpdated
     */
    protected function applyDescriptionUpdated(
      DescriptionUpdated $descriptionUpdated
    ) {
        $document = $this->loadDocumentFromRepository($descriptionUpdated);

        $eventLd = $document->getBody();
        $eventLd->description->{'nl'} = $descriptionUpdated->getDescription();

        $this->repository->save($document->withBody($eventLd));
    }

    /**
     * Apply the typical age range updated event to the event repository.
     * @param TypicalAgeRangeUpdated $typicalAgeRangeUpdated
     */
    protected function applyTypicalAgeRangeUpdated(
        TypicalAgeRangeUpdated $typicalAgeRangeUpdated
    ) {
        $document = $this->loadDocumentFromRepository($typicalAgeRangeUpdated);

        $eventLd = $document->getBody();

        if ($typicalAgeRangeUpdated->getTypicalAgeRange() === "-1") {
          unset($eventLd->typicalAgeRange);
        }
        else {
          $eventLd->typicalAgeRange = $typicalAgeRangeUpdated->getTypicalAgeRange();
        }

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
        return $this->loadDocumentFromRepositoryByEventId($event->getEventId());
    }

    /**
     * @param string $eventId
     * @return JsonDocument
     */
    protected function loadDocumentFromRepositoryByEventId($eventId)
    {
        $document = $this->repository->get($eventId);

        if (!$document) {
            return $this->newDocument($eventId);
        }

        return $document;
    }

    private function generateSameAs($eventId, $name)
    {
        $eventSlug = $this->slugger->slug($name);
        return array(
            'http://www.uitinvlaanderen.be/agenda/e/' . $eventSlug . '/' . $eventId,
        );
    }

    public function addDescriptionFilter(StringFilterInterface $filter)
    {
        $this->cdbXMLImporter->addDescriptionFilter($filter);
    }
}
