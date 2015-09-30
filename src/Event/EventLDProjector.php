<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Event;

use Broadway\Domain\DateTime;
use Broadway\Domain\DomainMessage;
use Broadway\EventHandling\EventListenerInterface;
use CultuurNet\UDB3\Cdb\EventItemFactory;
use CultuurNet\UDB3\CulturefeedSlugger;
use CultuurNet\UDB3\EntityNotFoundException;
use CultuurNet\UDB3\Event\Events\BookingInfoUpdated;
use CultuurNet\UDB3\Event\Events\ContactPointUpdated;
use CultuurNet\UDB3\Event\Events\DescriptionUpdated;
use CultuurNet\UDB3\Event\Events\EventCreated;
use CultuurNet\UDB3\Event\Events\EventDeleted;
use CultuurNet\UDB3\Event\Events\EventImportedFromUDB2;
use CultuurNet\UDB3\Event\Events\EventUpdatedFromUDB2;
use CultuurNet\UDB3\Event\Events\EventWasLabelled;
use CultuurNet\UDB3\Event\Events\ImageAdded;
use CultuurNet\UDB3\Event\Events\ImageDeleted;
use CultuurNet\UDB3\Event\Events\ImageUpdated;
use CultuurNet\UDB3\Event\Events\MajorInfoUpdated;
use CultuurNet\UDB3\Event\Events\OrganizerDeleted;
use CultuurNet\UDB3\Event\Events\OrganizerUpdated;
use CultuurNet\UDB3\Event\Events\TypicalAgeRangeDeleted;
use CultuurNet\UDB3\Event\Events\TypicalAgeRangeUpdated;
use CultuurNet\UDB3\Event\Events\Unlabelled;
use CultuurNet\UDB3\Event\ReadModel\DocumentRepositoryInterface;
use CultuurNet\UDB3\Event\ReadModel\JSONLD\CdbXMLImporter;
use CultuurNet\UDB3\Event\ReadModel\JSONLD\OrganizerServiceInterface;
use CultuurNet\UDB3\Event\ReadModel\JSONLD\PlaceServiceInterface;
use CultuurNet\UDB3\EventHandling\DelegateEventHandlingToSpecificMethodTrait;
use CultuurNet\UDB3\EventServiceInterface;
use CultuurNet\UDB3\Iri\IriGeneratorInterface;
use CultuurNet\UDB3\Label;
use CultuurNet\UDB3\Organizer\OrganizerProjectedToJSONLD;
use CultuurNet\UDB3\OrganizerService;
use CultuurNet\UDB3\Place\PlaceProjectedToJSONLD;
use CultuurNet\UDB3\PlaceService;
use CultuurNet\UDB3\ReadModel\JsonDocument;
use CultuurNet\UDB3\SluggerInterface;
use CultuurNet\UDB3\StringFilter\StringFilterInterface;
use CultuurNet\UDB3\Theme;

class EventLDProjector implements EventListenerInterface, PlaceServiceInterface, OrganizerServiceInterface
{
    use DelegateEventHandlingToSpecificMethodTrait;

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
     * @param OrganizerService $organizerService
     */
    public function __construct(
        DocumentRepositoryInterface $repository,
        IriGeneratorInterface $iriGenerator,
        EventServiceInterface $eventService,
        PlaceService $placeService,
        OrganizerService $organizerService
    ) {
        $this->repository = $repository;
        $this->iriGenerator = $iriGenerator;
        $this->organizerService = $organizerService;
        $this->placeService = $placeService;
        $this->eventService = $eventService;

        $this->slugger = new CulturefeedSlugger();
        $this->cdbXMLImporter = new CdbXMLImporter();
    }

    protected function applyOrganizerProjectedToJSONLD(OrganizerProjectedToJSONLD $organizerProjectedToJSONLD)
    {
        $eventIds = $this->eventsOrganizedByOrganizer(
            $organizerProjectedToJSONLD->getId()
        );

        $organizer = $this->organizerService->getEntity(
            $organizerProjectedToJSONLD->getId()
        );

        foreach ($eventIds as $eventId) {
            $document = $this->loadDocumentFromRepositoryByEventId(
                $eventId
            );
            $eventLD = $document->getBody();

            $newEventLD = clone $eventLD;
            $newEventLD->organizer = json_decode($organizer);

            if ($newEventLD != $eventLD) {
                $this->repository->save($document->withBody($newEventLD));
            }
        }
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

            $newEventLD = clone $eventLD;
            $newEventLD->location = json_decode($placeJSONLD);

            if ($newEventLD != $eventLD) {
                $this->repository->save($document->withBody($newEventLD));
            }
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
        $this->applyEventCdbXml(
            $eventImportedFromUDB2->getEventId(),
            $eventImportedFromUDB2->getCdbXmlNamespaceUri(),
            $eventImportedFromUDB2->getCdbXml()
        );
    }

    /**
     * @param EventUpdatedFromUDB2 $eventUpdatedFromUDB2
     */
    public function applyEventUpdatedFromUDB2(
        EventUpdatedFromUDB2 $eventUpdatedFromUDB2
    ) {
        $this->applyEventCdbXml(
            $eventUpdatedFromUDB2->getEventId(),
            $eventUpdatedFromUDB2->getCdbXmlNamespaceUri(),
            $eventUpdatedFromUDB2->getCdbXml()
        );
    }

    /**
     * @param string $eventId
     * @param string $cdbXmlNamespaceUri
     * @param string $cdbXml
     */
    protected function applyEventCdbXml(
        $eventId,
        $cdbXmlNamespaceUri,
        $cdbXml
    ) {
        $udb2Event = EventItemFactory::createEventFromCdbXml(
            $cdbXmlNamespaceUri,
            $cdbXml
        );

        $document = $this->newDocument($eventId);
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
     * @param DomainMessage $domainMessage
     */
    protected function applyEventCreated(
        EventCreated $eventCreated,
        DomainMessage $domainMessage
    ) {
        $document = $this->newDocument($eventCreated->getEventId());

        $jsonLD = $document->getBody();

        $jsonLD->{'@id'} = $this->iriGenerator->iri(
            $eventCreated->getEventId()
        );
        $jsonLD->name['nl'] = $eventCreated->getTitle();
        $jsonLD->location = array(
          '@type' => 'Place',
        ) + (array)$this->placeJSONLD($eventCreated->getLocation()->getCdbid());

        $calendarJsonLD = $eventCreated->getCalendar()->toJsonLd();
        $jsonLD = (object) array_merge((array) $jsonLD, $calendarJsonLD);

        // Same as.
        $jsonLD->sameAs = $this->generateSameAs(
            $eventCreated->getEventId(),
            reset($jsonLD->name)
        );

        $eventType = $eventCreated->getEventType();
        $jsonLD->terms = [
            $eventType->toJsonLd()
        ];

        $theme = $eventCreated->getTheme();
        if (!empty($theme)) {
            $jsonLD->terms[] = $theme->toJsonLd();
        }

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

    /**
     * @param EventDeleted $eventDeleted
     */
    public function applyEventDeleted(EventDeleted $eventDeleted)
    {
        $this->repository->remove($eventDeleted->getEventId());
    }

    /**
     * Apply the major info updated command to the projector.
     */
    public function applyMajorInfoUpdated(MajorInfoUpdated $majorInfoUpdated)
    {

        $document = $this->loadDocumentFromRepository($majorInfoUpdated);
        $jsonLD = $document->getBody();

        $jsonLD->name->nl = $majorInfoUpdated->getTitle();
        $jsonLD->location = array(
          '@type' => 'Place',
        ) + (array)$this->placeJSONLD($majorInfoUpdated->getLocation()->getCdbid());

        $calendarJsonLD = $majorInfoUpdated->getCalendar()->toJsonLd();
        $jsonLD = (object) array_merge((array) $jsonLD, $calendarJsonLD);

        // Remove old theme and event type.
        $jsonLD->terms = array_filter($jsonLD->terms, function ($term) {
            return $term->domain !== EventType::DOMAIN &&  $term->domain !== Theme::DOMAIN;
        });

        $eventType = $majorInfoUpdated->getEventType();
        $jsonLD->terms = [
            $eventType->toJsonLd()
        ];

        $theme = $majorInfoUpdated->getTheme();
        if (!empty($theme)) {
            $jsonLD->terms[] = $theme->toJsonLd();
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
     * @param EventWasLabelled $eventWasLabelled
     */
    public function applyEventWasLabelled(EventWasLabelled $eventWasLabelled)
    {
        $document = $this->loadDocumentFromRepository($eventWasLabelled);

        $eventLd = $document->getBody();

        $labels = isset($eventLd->labels) ? $eventLd->labels : [];
        $label = (string) $eventWasLabelled->getLabel();

        $labels[] = $label;
        $eventLd->labels = array_unique($labels);

        $this->repository->save($document->withBody($eventLd));
    }

    public function applyUnlabelled(Unlabelled $unlabelled)
    {
        $document = $this->loadDocumentFromRepository($unlabelled);

        $eventLd = $document->getBody();

        if (is_array($eventLd->labels)) {
            $eventLd->labels = array_filter(
                $eventLd->labels,
                function ($label) use ($unlabelled) {
                    return !$unlabelled->getLabel()->equals(
                        new Label($label)
                    );
                }
            );
            // Ensure array keys start with 0 so json_encode() does encode it
            // as an array and not as an object.
            $eventLd->labels = array_values($eventLd->labels);
        }

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
    public function applyDescriptionUpdated(
        DescriptionUpdated $descriptionUpdated
    ) {
        $document = $this->loadDocumentFromRepository($descriptionUpdated);

        $eventLd = $document->getBody();
        if (empty($eventLd->description)) {
            $eventLd->description = new \stdClass();
        }
        $eventLd->description->{'nl'} = $descriptionUpdated->getDescription();

        $this->repository->save($document->withBody($eventLd));
    }

    /**
     * Apply the typical age range updated event to the event repository.
     * @param TypicalAgeRangeUpdated $typicalAgeRangeUpdated
     */
    public function applyTypicalAgeRangeUpdated(
        TypicalAgeRangeUpdated $typicalAgeRangeUpdated
    ) {
        $document = $this->loadDocumentFromRepository($typicalAgeRangeUpdated);

        $eventLd = $document->getBody();
        $eventLd->typicalAgeRange = $typicalAgeRangeUpdated->getTypicalAgeRange();

        $this->repository->save($document->withBody($eventLd));
    }

    /**
     * Apply the typical age range deleted event to the event repository.
     * @param TypicalAgeRangeDeleted $typicalAgeRangeDeleted
     */
    public function applyTypicalAgeRangeDeleted(
        TypicalAgeRangeDeleted $typicalAgeRangeDeleted
    ) {
        $document = $this->loadDocumentFromRepository($typicalAgeRangeDeleted);

        $eventLd = $document->getBody();

        unset($eventLd->typicalAgeRange);

        $this->repository->save($document->withBody($eventLd));
    }

    /**
     * Apply the organizer updated event to the event repository.
     * @param OrganizerUpdated $organizerUpdated
     */
    public function applyOrganizerUpdated(OrganizerUpdated $organizerUpdated)
    {

        $document = $this->loadDocumentFromRepository($organizerUpdated);

        $eventLd = $document->getBody();

        $eventLd->organizer = array(
          '@type' => 'Organizer',
        ) + (array)$this->organizerJSONLD($organizerUpdated->getOrganizerId());

        $this->repository->save($document->withBody($eventLd));
    }

    /**
     * Apply the organizer delete event to the event repository.
     * @param OrganizerDeleted $organizerDeleted
     */
    public function applyOrganizerDeleted(OrganizerDeleted $organizerDeleted)
    {

        $document = $this->loadDocumentFromRepository($organizerDeleted);

        $eventLd = $document->getBody();

        unset($eventLd->organizer);

        $this->repository->save($document->withBody($eventLd));
    }

    /**
     * Apply the contact info updated event to the event repository.
     * @param ContactPointUpdated $contactPointUpdated
     */
    public function applyContactPointUpdated(ContactPointUpdated $contactPointUpdated)
    {

        $document = $this->loadDocumentFromRepository($contactPointUpdated);

        $eventLd = $document->getBody();
        $eventLd->contactPoint = $contactPointUpdated->getContactPoint()->toJsonLd();

        $this->repository->save($document->withBody($eventLd));
    }

    /**
     * Apply the booking info updated event to the event repository.
     * @param BookingInfoUpdated $bookingInfoUpdated
     */
    public function applyBookingInfoUpdated(BookingInfoUpdated $bookingInfoUpdated)
    {

        $document = $this->loadDocumentFromRepository($bookingInfoUpdated);

        $eventLd = $document->getBody();
        $eventLd->bookingInfo = $bookingInfoUpdated->getBookingInfo()->toJsonLd();

        $this->repository->save($document->withBody($eventLd));

    }

    /**
     * Apply the imageAdded event to the event repository.
     *
     * @param ImageAdded $imageAdded
     */
    public function applyImageAdded(ImageAdded $imageAdded)
    {

        $document = $this->loadDocumentFromRepository($imageAdded);

        $eventLd = $document->getBody();
        $eventLd->mediaObject = isset($eventLd->mediaObject) ? $eventLd->mediaObject : [];
        $eventLd->mediaObject[] = $imageAdded->getMediaObject()->toJsonLd();

        $this->repository->save($document->withBody($eventLd));

    }

    /**
     * Apply the ImageUpdated event to the event repository.
     *
     * @param ImageUpdated $imageUpdated
     */
    public function applyImageUpdated(ImageUpdated $imageUpdated)
    {

        $document = $this->loadDocumentFromRepository($imageUpdated);

        $eventLd = $document->getBody();
        $eventLd->mediaObject = isset($eventLd->mediaObject) ? $eventLd->mediaObject : [];
        $eventLd->mediaObject[$imageUpdated->getIndexToUpdate()] = $imageUpdated->getMediaObject()->toJsonLd();

        $this->repository->save($document->withBody($eventLd));

    }

    /**
     * Apply the imageDeleted event to the event repository.
     *
     * @param ImageDeleted $imageDeleted
     */
    public function applyImageDeleted(ImageDeleted $imageDeleted)
    {

        $document = $this->loadDocumentFromRepository($imageDeleted);

        $eventLd = $document->getBody();
        unset($eventLd->mediaObject[$imageDeleted->getIndexToDelete()]);

        // Generate new numeric keys.
        $eventLd->mediaObject = array_values($eventLd->mediaObject);

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
