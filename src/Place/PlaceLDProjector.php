<?php

/**
 * @file
 * Contains \Cultuurnet\UDB3\Place\PlaceLDProjector.
 */

namespace CultuurNet\UDB3\Place;

use Broadway\Domain\DateTime;
use Broadway\Domain\DomainEventStream;
use Broadway\Domain\DomainMessage;
use Broadway\Domain\DomainMessageInterface;
use Broadway\Domain\Metadata;
use Broadway\EventHandling\EventBusInterface;
use Broadway\UuidGenerator\Rfc4122\Version4Generator;
use CultuurNet\UDB3\Actor\ActorImportedFromUDB2;
use CultuurNet\UDB3\Actor\ActorLDProjector;
use CultuurNet\UDB3\Cdb\ActorItemFactory;
use CultuurNet\UDB3\CulturefeedSlugger;
use CultuurNet\UDB3\EntityNotFoundException;
use CultuurNet\UDB3\EntityServiceInterface;
use CultuurNet\UDB3\Event\EventType;
use CultuurNet\UDB3\Event\ReadModel\DocumentRepositoryInterface;
use CultuurNet\UDB3\Event\ReadModel\JsonDocument;
use CultuurNet\UDB3\Event\ReadModel\JSONLD\OrganizerServiceInterface;
use CultuurNet\UDB3\Facility;
use CultuurNet\UDB3\Iri\IriGeneratorInterface;
use CultuurNet\UDB3\OrganizerService;
use CultuurNet\UDB3\Place\Events\BookingInfoUpdated;
use CultuurNet\UDB3\Place\Events\ContactPointUpdated;
use CultuurNet\UDB3\Place\Events\DescriptionUpdated;
use CultuurNet\UDB3\Place\Events\FacilitiesUpdated;
use CultuurNet\UDB3\Place\Events\ImageAdded;
use CultuurNet\UDB3\Place\Events\ImageDeleted;
use CultuurNet\UDB3\Place\Events\ImageUpdated;
use CultuurNet\UDB3\Place\Events\MajorInfoUpdated;
use CultuurNet\UDB3\Place\Events\OrganizerDeleted;
use CultuurNet\UDB3\Place\Events\OrganizerUpdated;
use CultuurNet\UDB3\Place\Events\PlaceCreated;
use CultuurNet\UDB3\Place\Events\TypicalAgeRangeUpdated;
use CultuurNet\UDB3\Place\ReadModel\JSONLD\CdbXMLImporter;
use CultuurNet\UDB3\SluggerInterface;
use CultuurNet\UDB3\Theme;

class PlaceLDProjector extends ActorLDProjector
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
     * @param OrganizerServiceInterface $organiserService
     */
    public function __construct(
        DocumentRepositoryInterface $repository,
        IriGeneratorInterface $iriGenerator,
        EntityServiceInterface $organizerService,
        EventBusInterface $eventBus
    ) {
        $this->repository = $repository;
        $this->iriGenerator = $iriGenerator;
        $this->organizerService = $organizerService;
        $this->eventBus = $eventBus;
        $this->slugger = new CulturefeedSlugger();
        $this->cdbXMLImporter = new CdbXMLImporter();
    }

    /**
     * @param ActorImportedFromUDB2 $actorImportedFromUDB2
     */
    public function applyPlaceImportedFromUDB2(
        ActorImportedFromUDB2 $actorImportedFromUDB2
    ) {
        $udb2Actor = ActorItemFactory::createActorFromCdbXml(
            $actorImportedFromUDB2->getCdbXmlNamespaceUri(),
            $actorImportedFromUDB2->getCdbXml()
        );

        $document = $this->newDocument($actorImportedFromUDB2->getActorId());
        $actorLd = $document->getBody();

        $cdbXMLImporter = new CdbXMLImporter();
        $actorLd = $cdbXMLImporter->documentWithCdbXML(
            $actorLd,
            $udb2Actor
        );

        $this->repository->save($document->withBody($actorLd));

        $this->publishJSONLDUpdated(
            $actorImportedFromUDB2->getActorId()
        );
    }

    protected function publishJSONLDUpdated($id)
    {
        $generator = new Version4Generator();
        $events[] = DomainMessage::recordNow(
            $generator->generate(),
            1,
            new Metadata(),
            new PlaceProjectedToJSONLD($id)
        );

        $this->eventBus->publish(
            new DomainEventStream($events)
        );
    }

    /**
     * @param string $id
     * @return JsonDocument
     */
    protected function newDocument($id)
    {
        $document = new JsonDocument($id);

        $placeLd = $document->getBody();
        $placeLd->{'@id'} = $this->iriGenerator->iri($id);

        // @todo provide Event-LD context here relative to the base URI
        $placeLd->{'@context'} = '/api/1.0/place.jsonld';

        return $document->withBody($placeLd);
    }

    /**
     * @param PlaceCreated $placeCreated
     */
    protected function applyPlaceCreated(PlaceCreated $placeCreated, DomainMessageInterface $domainMessage)
    {
        $document = $this->newDocument($placeCreated->getPlaceId());

        $jsonLD = $document->getBody();

        $jsonLD->{'@id'} = $this->iriGenerator->iri(
            $placeCreated->getPlaceId()
        );
        $jsonLD->name = $placeCreated->getTitle();

        $jsonLD->address = $placeCreated->getAddress()->toJsonLd();

        $calendarJsonLD = $placeCreated->getCalendar()->toJsonLd();
        $jsonLD = (object) array_merge((array) $jsonLD, $calendarJsonLD);

        $eventType = $placeCreated->getEventType();
        $jsonLD->terms = [
            $eventType->toJsonLd()
        ];

        $theme = $placeCreated->getTheme();
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
     * Apply the major info updated command to the projector.
     */
    public function applyMajorInfoUpdated(MajorInfoUpdated $majorInfoUpdated)
    {

        $document = $this->loadPlaceDocumentFromRepository($majorInfoUpdated);
        $jsonLD = $document->getBody();

        $jsonLD->name = $majorInfoUpdated->getTitle();
        $jsonLD->address = $majorInfoUpdated->getAddress()->toJsonLd();

        $calendarJsonLD = $majorInfoUpdated->getCalendar()->toJsonLd();
        $jsonLD = (object) array_merge((array) $jsonLD, $calendarJsonLD);

        // Remove old theme and event type.
        $jsonLD->terms = array_filter($jsonLD->terms, function($term) {
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

    /**
     * Apply the description updated event to the place repository.
     * @param DescriptionUpdated $descriptionUpdated
     */
    protected function applyDescriptionUpdated(
        DescriptionUpdated $descriptionUpdated
    ) {

        $document = $this->loadPlaceDocumentFromRepository($descriptionUpdated);

        $placeLD = $document->getBody();
        $placeLD->description->{'nl'} = $descriptionUpdated->getDescription();

        $this->repository->save($document->withBody($placeLD));
    }

    /**
     * Apply the booking info updated event to the event repository.
     * @param BookingInfoUpdated $bookingInfoUpdated
     */
    protected function applyBookingInfoUpdated(BookingInfoUpdated $bookingInfoUpdated)
    {

        $document = $this->loadPlaceDocumentFromRepository($bookingInfoUpdated);

        $placeLD = $document->getBody();
        $placeLD->bookingInfo = $bookingInfoUpdated->getBookingInfo()->toJsonLd();

        $this->repository->save($document->withBody($placeLD));

    }

    /**
     * Apply the typical age range updated event to the event repository.
     * @param TypicalAgeRangeUpdated $typicalAgeRangeUpdated
     */
    protected function applyTypicalAgeRangeUpdated(
        TypicalAgeRangeUpdated $typicalAgeRangeUpdated
    ) {
        $document = $this->loadPlaceDocumentFromRepository($typicalAgeRangeUpdated);

        $eventLd = $document->getBody();

        if ($typicalAgeRangeUpdated->getTypicalAgeRange() === "-1") {
            unset($eventLd->typicalAgeRange);
        } else {
            $eventLd->typicalAgeRange = $typicalAgeRangeUpdated->getTypicalAgeRange();
        }

        $this->repository->save($document->withBody($eventLd));
    }

    /**
     * Apply the organizer updated event to the event repository.
     * @param OrganizerUpdated $organizerUpdated
     */
    protected function applyOrganizerUpdated(OrganizerUpdated $organizerUpdated)
    {

        $document = $this->loadPlaceDocumentFromRepository($organizerUpdated);

        $placeLd = $document->getBody();

        $placeLd->organizer = array(
          '@type' => 'Organizer',
        ) + (array)$this->organizerJSONLD($organizerUpdated->getOrganizerId());

        $this->repository->save($document->withBody($placeLd));
    }

    /**
     * Apply the organizer delete event to the event repository.
     * @param OrganizerDeleted $organizerDeleted
     */
    protected function applyOrganizerDeleted(OrganizerDeleted $organizerDeleted)
    {

        $document = $this->loadPlaceDocumentFromRepository($organizerDeleted);

        $placeLd = $document->getBody();

        unset($placeLd->organizer);

        $this->repository->save($document->withBody($placeLd));
    }

    /**
     * Apply the contact point updated event to the event repository.
     * @param ContactPointUpdated $contactPointUpdated
     */
    protected function applyContactPointUpdated(ContactPointUpdated $contactPointUpdated)
    {

        $document = $this->loadPlaceDocumentFromRepository($contactPointUpdated);

        $placeLd = $document->getBody();
        $placeLd->contactPoint = $contactPointUpdated->getContactPoint()->toJsonLd();

        $this->repository->save($document->withBody($placeLd));
    }

    /**
     * Apply the facilitiesupdated event to the event repository.
     * @param FacilitiesUpdated $facilitiesUpdated
     */
    protected function applyFacilitiesUpdated(FacilitiesUpdated $facilitiesUpdated)
    {

        $document = $this->loadPlaceDocumentFromRepository($facilitiesUpdated);

        $placeLd = $document->getBody();

        $terms = isset($placeLd->terms) ? $placeLd->terms : array();

        // Remove all old facilities + get numeric keys.
        $terms = array_values(array_filter(
            $terms,
            function ($term) {
                return $term->domain !== Facility::DOMAIN;
            }
        ));

        // Add the new facilities.
        foreach ($facilitiesUpdated->getFacilities() as $facility) {
            $terms[] = $facility->toJsonLd();
        }

        $placeLd->terms = $terms;

        $this->repository->save($document->withBody($placeLd));

    }

    /**
     * Apply the imageAdded event to the place repository.
     *
     * @param ImageAdded $imageAdded
     */
    protected function applyImageAdded(ImageAdded $imageAdded)
    {

        $document = $this->loadPlaceDocumentFromRepository($imageAdded);

        $placeLd = $document->getBody();
        $placeLd->mediaObject = isset($placeLd->mediaObject) ? $placeLd->mediaObject : [];
        $placeLd->mediaObject[] = $imageAdded->getMediaObject()->toJsonLd();

        $this->repository->save($document->withBody($placeLd));

    }

    /**
     * Apply the ImageUpdated event to the place repository.
     *
     * @param ImageUpdated $imageUpdated
     */
    protected function applyImageUpdated(ImageUpdated $imageUpdated)
    {

        $document = $this->loadPlaceDocumentFromRepository($imageUpdated);

        $placeLd = $document->getBody();
        $placeLd->mediaObject = isset($placeLd->mediaObject) ? $placeLd->mediaObject : [];
        $placeLd->mediaObject[$imageUpdated->getIndexToUpdate()] = $imageUpdated->getMediaObject()->toJsonLd();

        $this->repository->save($document->withBody($placeLd));
    }

    /**
     * Apply the imageDeleted event to the place repository.
     *
     * @param ImageDeleted $imageDeleted
     */
    protected function applyImageDeleted(ImageDeleted $imageDeleted)
    {

        $document = $this->loadPlaceDocumentFromRepository($imageDeleted);

        $placeLd = $document->getBody();
        unset($placeLd->mediaObject[$imageDeleted->getIndexToDelete()]);

        // Generate new numeric keys.
        $placeLd->mediaObject = array_values($placeLd->mediaObject);

        $this->repository->save($document->withBody($placeLd));

    }

    /**
     * @param PlaceEvent $place
     * @return JsonDocument
     */
    protected function loadPlaceDocumentFromRepository(PlaceEvent $place)
    {
        $document = $this->repository->get($place->getPlaceId());

        if (!$document) {
            return $this->newDocument($place->getPlaceId());
        }

        return $document;
    }

    /**
     * Get the organizer jsonLD.
     */
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
}
