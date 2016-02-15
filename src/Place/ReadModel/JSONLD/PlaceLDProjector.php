<?php

namespace CultuurNet\UDB3\Place\ReadModel\JSONLD;

use Broadway\Domain\DateTime;
use Broadway\Domain\DomainMessage;
use Broadway\EventHandling\EventListenerInterface;
use CultuurNet\UDB3\Cdb\ActorItemFactory;
use CultuurNet\UDB3\Cdb\EventItemFactory;
use CultuurNet\UDB3\CulturefeedSlugger;
use CultuurNet\UDB3\EntityNotFoundException;
use CultuurNet\UDB3\EntityServiceInterface;
use CultuurNet\UDB3\Event\EventType;
use CultuurNet\UDB3\Event\ReadModel\DocumentRepositoryInterface;
use CultuurNet\UDB3\EventHandling\DelegateEventHandlingToSpecificMethodTrait;
use CultuurNet\UDB3\Facility;
use CultuurNet\UDB3\Iri\IriGeneratorInterface;
use CultuurNet\UDB3\Offer\ReadModel\JSONLD\CdbXMLItemBaseImporter;
use CultuurNet\UDB3\Offer\ReadModel\JSONLD\OfferLDProjector;
use CultuurNet\UDB3\Place\Events\BookingInfoUpdated;
use CultuurNet\UDB3\Place\Events\ContactPointUpdated;
use CultuurNet\UDB3\Place\Events\DescriptionTranslated;
use CultuurNet\UDB3\Place\Events\DescriptionUpdated;
use CultuurNet\UDB3\Place\Events\FacilitiesUpdated;
use CultuurNet\UDB3\Place\Events\ImageAdded;
use CultuurNet\UDB3\Place\Events\ImageDeleted;
use CultuurNet\UDB3\Place\Events\ImageUpdated;
use CultuurNet\UDB3\Place\Events\LabelAdded;
use CultuurNet\UDB3\Place\Events\LabelDeleted;
use CultuurNet\UDB3\Place\Events\MajorInfoUpdated;
use CultuurNet\UDB3\Place\Events\OrganizerDeleted;
use CultuurNet\UDB3\Place\Events\OrganizerUpdated;
use CultuurNet\UDB3\Place\Events\PlaceCreated;
use CultuurNet\UDB3\Place\Events\PlaceDeleted;
use CultuurNet\UDB3\Place\Events\PlaceImportedFromUDB2;
use CultuurNet\UDB3\Place\Events\PlaceImportedFromUDB2Event;
use CultuurNet\UDB3\Place\Events\TitleTranslated;
use CultuurNet\UDB3\Place\Events\TypicalAgeRangeDeleted;
use CultuurNet\UDB3\Place\Events\TypicalAgeRangeUpdated;
use CultuurNet\UDB3\Place\PlaceEvent;
use CultuurNet\UDB3\Place\ReadModel\JSONLD\CdbXMLImporter;
use CultuurNet\UDB3\ReadModel\JsonDocument;
use CultuurNet\UDB3\SluggerInterface;
use CultuurNet\UDB3\Theme;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Projects state changes on Place entities to a JSON-LD read model in a
 * document repository.
 */
class PlaceLDProjector extends OfferLDProjector implements EventListenerInterface
{
    /**
     * @var SerializerInterface
     */
    protected $mediaObjectSerializer;

    /**
     * @param DocumentRepositoryInterface $repository
     * @param IriGeneratorInterface $iriGenerator
     * @param EntityServiceInterface $organizerService
     * @param SerializerInterface $mediaObjectSerializer
     */
    public function __construct(
        DocumentRepositoryInterface $repository,
        IriGeneratorInterface $iriGenerator,
        EntityServiceInterface $organizerService,
        SerializerInterface $mediaObjectSerializer
    ) {
        parent::__construct(
            $repository,
            $iriGenerator,
            $organizerService
        );

        $this->mediaObjectSerializer = $mediaObjectSerializer;
        $this->slugger = new CulturefeedSlugger();
        $this->cdbXMLImporter = new CdbXMLImporter(
            new CdbXMLItemBaseImporter()
        );
    }

    /**
     * @param PlaceImportedFromUDB2 $actorImportedFromUDB2
     */
    protected function applyPlaceImportedFromUDB2(
        PlaceImportedFromUDB2 $actorImportedFromUDB2
    ) {
        $udb2Actor = ActorItemFactory::createActorFromCdbXml(
            $actorImportedFromUDB2->getCdbXmlNamespaceUri(),
            $actorImportedFromUDB2->getCdbXml()
        );

        $document = $this->newDocument($actorImportedFromUDB2->getActorId());
        $actorLd = $document->getBody();

        $actorLd = $this->cdbXMLImporter->documentWithCdbXML(
            $actorLd,
            $udb2Actor
        );

        $this->repository->save($document->withBody($actorLd));
    }


    /**
     * @param PlaceImportedFromUDB2Event $eventImportedFromUDB2
     */
    protected function applyPlaceImportedFromUDB2Event(
        PlaceImportedFromUDB2Event $eventImportedFromUDB2
    ) {
        $udb2Event = EventItemFactory::createEventFromCdbXml(
            $eventImportedFromUDB2->getCdbXmlNamespaceUri(),
            $eventImportedFromUDB2->getCdbXml()
        );

        $document = $this->newDocument($eventImportedFromUDB2->getActorId());
        $placeLd = $document->getBody();

        $placeLd = $this->cdbXMLImporter->eventDocumentWithCdbXML(
            $placeLd,
            $udb2Event
        );

        $this->repository->save($document->withBody($placeLd));
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
     * @param DomainMessage $domainMessage
     */
    protected function applyPlaceCreated(PlaceCreated $placeCreated, DomainMessage $domainMessage)
    {
        $document = $this->newDocument($placeCreated->getPlaceId());

        $jsonLD = $document->getBody();

        $jsonLD->{'@id'} = $this->iriGenerator->iri(
            $placeCreated->getPlaceId()
        );

        if (empty($jsonLD->name)) {
            $jsonLD->name = new \stdClass();
        }

        $jsonLD->name->nl = $placeCreated->getTitle();

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

        $jsonLD->modified = $jsonLD->created;

        $metaData = $domainMessage->getMetadata()->serialize();
        if (isset($metaData['user_email'])) {
            $jsonLD->creator = $metaData['user_email'];
        } elseif (isset($metaData['user_nick'])) {
            $jsonLD->creator = $metaData['user_nick'];
        }

        $this->repository->save($document->withBody($jsonLD));
    }

    /**
     * @param PlaceDeleted $placeDeleted
     */
    protected function applyPlaceDeleted(PlaceDeleted $placeDeleted)
    {
        $this->repository->remove($placeDeleted->getPlaceId());
    }

    /**
     * Apply the major info updated command to the projector.
     * @param MajorInfoUpdated $majorInfoUpdated
     */
    protected function applyMajorInfoUpdated(MajorInfoUpdated $majorInfoUpdated)
    {

        $document = $this->loadPlaceDocumentFromRepository($majorInfoUpdated);
        $jsonLD = $document->getBody();

        $jsonLD->name->nl = $majorInfoUpdated->getTitle();
        $jsonLD->address = $majorInfoUpdated->getAddress()->toJsonLd();

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

    /**
     * Apply the description updated event to the place repository.
     * @param DescriptionUpdated $descriptionUpdated
     */
    protected function applyDescriptionUpdated(
        DescriptionUpdated $descriptionUpdated
    ) {

        $document = $this->loadPlaceDocumentFromRepository($descriptionUpdated);

        $placeLD = $document->getBody();
        if (empty($placeLD->description)) {
            $placeLD->description = new \stdClass();
        }
        $placeLD->description->{'nl'} = $descriptionUpdated->getDescription();

        $this->repository->save($document->withBody($placeLD));
    }

    /**
     * Apply the booking info updated event to the place repository.
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
     * Apply the typical age range updated event to the place repository.
     * @param TypicalAgeRangeUpdated $typicalAgeRangeUpdated
     */
    protected function applyTypicalAgeRangeUpdated(
        TypicalAgeRangeUpdated $typicalAgeRangeUpdated
    ) {
        $document = $this->loadPlaceDocumentFromRepository($typicalAgeRangeUpdated);

        $placeLd = $document->getBody();
        $placeLd->typicalAgeRange = $typicalAgeRangeUpdated->getTypicalAgeRange();

        $this->repository->save($document->withBody($placeLd));
    }

    /**
     * Apply the typical age range deleted event to the place repository.
     * @param TypicalAgeRangeDeleted $typicalAgeRangeDeleted
     */
    protected function applyTypicalAgeRangeDeleted(
        TypicalAgeRangeDeleted $typicalAgeRangeDeleted
    ) {
        $document = $this->loadPlaceDocumentFromRepository($typicalAgeRangeDeleted);

        $placeLd = $document->getBody();

        unset($placeLd->typicalAgeRange);

        $this->repository->save($document->withBody($placeLd));
    }

    /**
     * Apply the organizer updated event to the place repository.
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
     * Apply the organizer delete event to the place repository.
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
     * Apply the contact point updated event to the place repository.
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
     * Apply the facilitiesupdated event to the place repository.
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

        $imageData = $this->mediaObjectSerializer->serialize(
            $imageAdded->getImage(),
            'json-ld'
        );
        $placeLd->mediaObject[] = $imageData;

        $this->repository->save($document->withBody($placeLd));

    }

    /**
     * Apply the ImageUpdated event to the place repository.
     *
     * @param ImageUpdated $imageUpdated
     */
    protected function applyImageUpdated(ImageUpdated $imageUpdated)
    {
        $document = $this->repository->get($imageUpdated->getItemId());

        if (!$document) {
            return $this->newDocument($imageUpdated->getItemId());
        }

        $placeLd = $document->getBody();

        if (!isset($placeLd->mediaObject)) {
            throw new \Exception('The image to update could not be found.');
        }

        $updatedMediaObjects = [];

        foreach ($placeLd->mediaObject as $mediaObject) {
            $mediaObjectMatches = (
                strpos(
                    $mediaObject->{'@id'},
                    (string)$imageUpdated->getMediaObjectId()
                ) > 0
            );

            if ($mediaObjectMatches) {
                $mediaObject->description = (string)$imageUpdated->getDescription();
                $mediaObject->copyrightHolder = (string)$imageUpdated->getCopyrightHolder();

                $updatedMediaObjects[] = $mediaObject;
            }
        };

        if (empty($updatedMediaObjects)) {
            throw new \Exception('The image to update could not be found.');
        }

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
     * @param string $organizerId
     * @return array
     */
    protected function organizerJSONLD($organizerId)
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
     * @return string
     */
    protected function getLabelAddedClassName()
    {
        return LabelAdded::class;
    }

    /**
     * @return string
     */
    protected function getLabelDeletedClassName()
    {
        return LabelDeleted::class;
    }

    /**
     * @return string
     */
    protected function getTitleTranslatedClassName()
    {
        return TitleTranslated::class;
    }

    /**
     * @return string
     */
    protected function getDescriptionTranslatedClassName()
    {
        return DescriptionTranslated::class;
    }
}
