<?php

namespace CultuurNet\UDB3\Place\ReadModel\JSONLD;

use Broadway\Domain\DateTime;
use Broadway\Domain\DomainMessage;
use Broadway\EventHandling\EventListenerInterface;
use CultuurNet\UDB3\Actor\ActorImportedFromUDB2;
use CultuurNet\UDB3\Cdb\ActorItemFactory;
use CultuurNet\UDB3\Cdb\EventItemFactory;
use CultuurNet\UDB3\CulturefeedSlugger;
use CultuurNet\UDB3\EntityServiceInterface;
use CultuurNet\UDB3\Event\EventType;
use CultuurNet\UDB3\Event\ReadModel\DocumentGoneException;
use CultuurNet\UDB3\Event\ReadModel\DocumentRepositoryInterface;
use CultuurNet\UDB3\Facility;
use CultuurNet\UDB3\Iri\IriGeneratorInterface;
use CultuurNet\UDB3\Offer\Item\Events\MainImageSelected;
use CultuurNet\UDB3\Offer\ReadModel\JSONLD\CdbXMLItemBaseImporter;
use CultuurNet\UDB3\Offer\ReadModel\JSONLD\OfferLDProjector;
use CultuurNet\UDB3\Offer\ReadModel\JSONLD\OfferUpdate;
use CultuurNet\UDB3\Offer\WorkflowStatus;
use CultuurNet\UDB3\Place\Events\BookingInfoUpdated;
use CultuurNet\UDB3\Place\Events\ContactPointUpdated;
use CultuurNet\UDB3\Place\Events\DescriptionTranslated;
use CultuurNet\UDB3\Place\Events\DescriptionUpdated;
use CultuurNet\UDB3\Place\Events\FacilitiesUpdated;
use CultuurNet\UDB3\Place\Events\ImageAdded;
use CultuurNet\UDB3\Place\Events\ImageRemoved;
use CultuurNet\UDB3\Place\Events\ImageUpdated;
use CultuurNet\UDB3\Place\Events\LabelAdded;
use CultuurNet\UDB3\Place\Events\LabelDeleted;
use CultuurNet\UDB3\Place\Events\MajorInfoUpdated;
use CultuurNet\UDB3\Place\Events\Moderation\Approved;
use CultuurNet\UDB3\Place\Events\Moderation\FlaggedAsDuplicate;
use CultuurNet\UDB3\Place\Events\Moderation\FlaggedAsInappropriate;
use CultuurNet\UDB3\Place\Events\Moderation\Published;
use CultuurNet\UDB3\Place\Events\Moderation\Rejected;
use CultuurNet\UDB3\Place\Events\OrganizerDeleted;
use CultuurNet\UDB3\Place\Events\OrganizerUpdated;
use CultuurNet\UDB3\Place\Events\PlaceCreated;
use CultuurNet\UDB3\Place\Events\PlaceDeleted;
use CultuurNet\UDB3\Place\Events\PlaceImportedFromUDB2;
use CultuurNet\UDB3\Place\Events\PlaceImportedFromUDB2Event;
use CultuurNet\UDB3\Place\Events\PlaceUpdatedFromUDB2;
use CultuurNet\UDB3\Place\Events\TitleTranslated;
use CultuurNet\UDB3\Place\Events\TypicalAgeRangeDeleted;
use CultuurNet\UDB3\Place\Events\TypicalAgeRangeUpdated;
use CultuurNet\UDB3\Place\PlaceEvent;
use CultuurNet\UDB3\ReadModel\JsonDocument;
use CultuurNet\UDB3\Theme;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Projects state changes on Place entities to a JSON-LD read model in a
 * document repository.
 */
class PlaceLDProjector extends OfferLDProjector implements EventListenerInterface
{
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
            $organizerService,
            $mediaObjectSerializer
        );

        $this->slugger = new CulturefeedSlugger();
        $this->cdbXMLImporter = new CdbXMLImporter(
            new CdbXMLItemBaseImporter()
        );
    }

    /**
     * @param PlaceImportedFromUDB2 $placeImportedFromUDB2
     */
    protected function applyPlaceImportedFromUDB2(
        PlaceImportedFromUDB2 $placeImportedFromUDB2
    ) {
        $this->projectActorImportedFromUDB2($placeImportedFromUDB2);
    }

    /**
     * @param PlaceUpdatedFromUDB2 $placeUpdatedFromUDB2
     */
    protected function applyPlaceUpdatedFromUDB2(
        PlaceUpdatedFromUDB2 $placeUpdatedFromUDB2
    ) {
        $this->projectActorImportedFromUDB2($placeUpdatedFromUDB2);
    }

    /**
     * @param ActorImportedFromUDB2 $actorImportedFromUDB2
     */
    protected function projectActorImportedFromUDB2(
        ActorImportedFromUDB2 $actorImportedFromUDB2
    ) {
        $actorId = $actorImportedFromUDB2->getActorId();

        $udb2Actor = ActorItemFactory::createActorFromCdbXml(
            $actorImportedFromUDB2->getCdbXmlNamespaceUri(),
            $actorImportedFromUDB2->getCdbXml()
        );

        try {
            $document = $this->loadPlaceDocumentFromRepositoryById($actorId);
        } catch (DocumentGoneException $e) {
            $document = $this->newDocument($actorId);
        }

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

        $jsonLD->workflowStatus = WorkflowStatus::DRAFT()->getName();

        $this->repository->save($document->withBody($jsonLD));
    }

    /**
     * @param PlaceDeleted $placeDeleted
     */
    protected function applyPlaceDeleted(PlaceDeleted $placeDeleted)
    {
        $this->repository->remove($placeDeleted->getItemId());
    }

    /**
     * Apply the major info updated command to the projector.
     * @param MajorInfoUpdated $majorInfoUpdated
     */
    protected function applyMajorInfoUpdated(MajorInfoUpdated $majorInfoUpdated)
    {
        $document = $this
            ->loadPlaceDocumentFromRepository($majorInfoUpdated)
            ->apply(OfferUpdate::calendar($majorInfoUpdated->getCalendar()));

        $jsonLD = $document->getBody();

        $jsonLD->name->nl = $majorInfoUpdated->getTitle();
        $jsonLD->address = $majorInfoUpdated->getAddress()->toJsonLd();

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
     * @param string $itemId
     * @return JsonDocument
     */
    protected function loadPlaceDocumentFromRepositoryById($itemId)
    {
        $document = $this->repository->get($itemId);

        if (!$document) {
            return $this->newDocument($itemId);
        }

        return $document;
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
    protected function getImageAddedClassName()
    {
        return ImageAdded::class;
    }

    /**
     * @return string
     */
    protected function getImageRemovedClassName()
    {
        return ImageRemoved::class;
    }

    /**
     * @return string
     */
    protected function getImageUpdatedClassName()
    {
        return ImageUpdated::class;
    }

    protected function getMainImageSelectedClassName()
    {
        return MainImageSelected::class;
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

    /**
     * @return string
     */
    protected function getOrganizerUpdatedClassName()
    {
        return OrganizerUpdated::class;
    }

    /**
     * @return string
     */
    protected function getOrganizerDeletedClassName()
    {
        return OrganizerDeleted::class;
    }

    protected function getBookingInfoUpdatedClassName()
    {
        return BookingInfoUpdated::class;
    }

    protected function getContactPointUpdatedClassName()
    {
        return ContactPointUpdated::class;
    }

    protected function getDescriptionUpdatedClassName()
    {
        return DescriptionUpdated::class;
    }

    protected function getTypicalAgeRangeUpdatedClassName()
    {
        return TypicalAgeRangeUpdated::class;
    }

    protected function getTypicalAgeRangeDeletedClassName()
    {
        return TypicalAgeRangeDeleted::class;
    }

    protected function getPublishedClassName()
    {
        return Published::class;
    }

    protected function getApprovedClassName()
    {
        return Approved::class;
    }

    protected function getRejectedClassName()
    {
        return Rejected::class;
    }

    protected function getFlaggedAsDuplicateClassName()
    {
        return FlaggedAsDuplicate::class;
    }

    protected function getFlaggedAsInappropriateClassName()
    {
        return FlaggedAsInappropriate::class;
    }
}
