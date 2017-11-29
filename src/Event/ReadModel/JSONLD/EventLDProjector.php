<?php

namespace CultuurNet\UDB3\Event\ReadModel\JSONLD;

use Broadway\Domain\DateTime;
use Broadway\Domain\DomainMessage;
use Broadway\Domain\Metadata;
use Broadway\EventHandling\EventListenerInterface;
use CultuurNet\UDB3\Cdb\EventItemFactory;
use CultuurNet\UDB3\EntityNotFoundException;
use CultuurNet\UDB3\Event\Events\AudienceUpdated;
use CultuurNet\UDB3\Event\Events\BookingInfoUpdated;
use CultuurNet\UDB3\Event\Events\CalendarUpdated;
use CultuurNet\UDB3\Event\Events\ContactPointUpdated;
use CultuurNet\UDB3\Event\Events\DescriptionTranslated;
use CultuurNet\UDB3\Event\Events\DescriptionUpdated;
use CultuurNet\UDB3\Event\Events\EventCopied;
use CultuurNet\UDB3\Event\Events\EventCreated;
use CultuurNet\UDB3\Event\Events\EventDeleted;
use CultuurNet\UDB3\Event\Events\EventImportedFromUDB2;
use CultuurNet\UDB3\Event\Events\EventUpdatedFromUDB2;
use CultuurNet\UDB3\Event\Events\ImageAdded;
use CultuurNet\UDB3\Event\Events\ImageRemoved;
use CultuurNet\UDB3\Event\Events\Image\ImagesImportedFromUDB2;
use CultuurNet\UDB3\Event\Events\Image\ImagesUpdatedFromUDB2;
use CultuurNet\UDB3\Event\Events\ImageUpdated;
use CultuurNet\UDB3\Event\Events\LabelAdded;
use CultuurNet\UDB3\Event\Events\LabelRemoved;
use CultuurNet\UDB3\Event\Events\LocationUpdated;
use CultuurNet\UDB3\Event\Events\MainImageSelected;
use CultuurNet\UDB3\Event\Events\MajorInfoUpdated;
use CultuurNet\UDB3\Event\Events\Moderation\Approved;
use CultuurNet\UDB3\Event\Events\Moderation\FlaggedAsDuplicate;
use CultuurNet\UDB3\Event\Events\Moderation\FlaggedAsInappropriate;
use CultuurNet\UDB3\Event\Events\Moderation\Published;
use CultuurNet\UDB3\Event\Events\Moderation\Rejected;
use CultuurNet\UDB3\Event\Events\OrganizerDeleted;
use CultuurNet\UDB3\Event\Events\OrganizerUpdated;
use CultuurNet\UDB3\Event\Events\PriceInfoUpdated;
use CultuurNet\UDB3\Event\Events\ThemeUpdated;
use CultuurNet\UDB3\Event\Events\TitleTranslated;
use CultuurNet\UDB3\Event\Events\TitleUpdated;
use CultuurNet\UDB3\Event\Events\TypeUpdated;
use CultuurNet\UDB3\Event\Events\TypicalAgeRangeDeleted;
use CultuurNet\UDB3\Event\Events\TypicalAgeRangeUpdated;
use CultuurNet\UDB3\Event\EventType;
use CultuurNet\UDB3\Event\ReadModel\DocumentGoneException;
use CultuurNet\UDB3\Event\ReadModel\DocumentRepositoryInterface;
use CultuurNet\UDB3\Event\EventServiceInterface;
use CultuurNet\UDB3\Event\ValueObjects\Audience;
use CultuurNet\UDB3\Event\ValueObjects\AudienceType;
use CultuurNet\UDB3\Iri\IriGeneratorInterface;
use CultuurNet\UDB3\Language;
use CultuurNet\UDB3\Offer\AvailableTo;
use CultuurNet\UDB3\Offer\IriOfferIdentifierFactoryInterface;
use CultuurNet\UDB3\Offer\ReadModel\JSONLD\OfferLDProjector;
use CultuurNet\UDB3\Offer\ReadModel\JSONLD\OfferUpdate;
use CultuurNet\UDB3\Offer\WorkflowStatus;
use CultuurNet\UDB3\Organizer\OrganizerProjectedToJSONLD;
use CultuurNet\UDB3\OrganizerService;
use CultuurNet\UDB3\Place\Events\PlaceProjectedToJSONLD;
use CultuurNet\UDB3\PlaceService;
use CultuurNet\UDB3\ReadModel\JsonDocument;
use CultuurNet\UDB3\ReadModel\JsonDocumentMetaDataEnricherInterface;
use CultuurNet\UDB3\Theme;
use Symfony\Component\Serializer\SerializerInterface;
use ValueObjects\StringLiteral\StringLiteral;
use ValueObjects\Web\Url;

/**
 * Projects state changes on Event entities to a JSON-LD read model in a
 * document repository.
 *
 * Implements PlaceServiceInterface and OrganizerServiceInterface to do a double
 * dispatch with CdbXMLImporter.
 */
class EventLDProjector extends OfferLDProjector implements
    EventListenerInterface,
    PlaceServiceInterface,
    OrganizerServiceInterface
{
    /**
     * @var PlaceService
     */
    protected $placeService;

    /**
     * @var EventServiceInterface
     */
    protected $eventService;

    /**
     * @var IriOfferIdentifierFactoryInterface
     */
    protected $iriOfferIdentifierFactory;

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
     * @param SerializerInterface $mediaObjectSerializer
     * @param IriOfferIdentifierFactoryInterface $iriOfferIdentifierFactory
     * @param CdbXMLImporter $cdbXMLImporter
     * @param JsonDocumentMetaDataEnricherInterface $jsonDocumentMetaDataEnricher
     */
    public function __construct(
        DocumentRepositoryInterface $repository,
        IriGeneratorInterface $iriGenerator,
        EventServiceInterface $eventService,
        PlaceService $placeService,
        OrganizerService $organizerService,
        SerializerInterface $mediaObjectSerializer,
        IriOfferIdentifierFactoryInterface $iriOfferIdentifierFactory,
        CdbXMLImporter $cdbXMLImporter,
        JsonDocumentMetaDataEnricherInterface $jsonDocumentMetaDataEnricher
    ) {
        parent::__construct(
            $repository,
            $iriGenerator,
            $organizerService,
            $mediaObjectSerializer,
            $jsonDocumentMetaDataEnricher
        );

        $this->placeService = $placeService;
        $this->eventService = $eventService;
        $this->cdbXMLImporter = $cdbXMLImporter;

        $this->iriOfferIdentifierFactory = $iriOfferIdentifierFactory;
    }

    /**
     * @param string $id
     * @return JsonDocument
     */
    protected function newDocument($id)
    {
        $document = new JsonDocument($id);

        $offerLd = $document->getBody();
        $offerLd->{'@id'} = $this->iriGenerator->iri($id);
        $offerLd->{'@context'} = '/contexts/event';

        return $document->withBody($offerLd);
    }

    protected function applyOrganizerProjectedToJSONLD(OrganizerProjectedToJSONLD $organizerProjectedToJSONLD)
    {
        $eventIds = $this->eventsOrganizedByOrganizer(
            $organizerProjectedToJSONLD->getId()
        );

        $organizer = $this->organizerService->getEntity(
            $organizerProjectedToJSONLD->getId()
        );

        $documents = [];

        foreach ($eventIds as $eventId) {
            $document = $this->loadDocumentFromRepositoryByItemId(
                $eventId
            );
            $eventLD = $document->getBody();

            $newEventLD = clone $eventLD;
            $newEventLD->organizer = json_decode($organizer);

            if ($newEventLD != $eventLD) {
                $documents[] = $document->withBody($newEventLD);
            }
        }

        return $documents;
    }

    protected function applyPlaceProjectedToJSONLD(
        PlaceProjectedToJSONLD $placeProjectedToJSONLD
    ) {
        $identifier = $this->iriOfferIdentifierFactory->fromIri(
            Url::fromNative($placeProjectedToJSONLD->getIri())
        );

        $eventsLocatedAtPlace = $this->eventsLocatedAtPlace(
            $identifier->getId()
        );

        $placeJSONLD = $this->placeService->getEntity(
            $identifier->getId()
        );

        $documents = [];

        foreach ($eventsLocatedAtPlace as $eventId) {
            $document = $this->loadDocumentFromRepositoryByItemId(
                $eventId
            );
            $eventLD = $document->getBody();

            $newEventLD = clone $eventLD;
            $newEventLD->location = json_decode($placeJSONLD);

            if ($newEventLD != $eventLD) {
                $documents[] = $document->withBody($newEventLD);
            }
        }

        return $documents;
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
     * @return JsonDocument
     */
    protected function applyEventImportedFromUDB2(
        EventImportedFromUDB2 $eventImportedFromUDB2
    ) {
        return $this->applyEventCdbXmlFromUDB2(
            $eventImportedFromUDB2->getEventId(),
            $eventImportedFromUDB2->getCdbXmlNamespaceUri(),
            $eventImportedFromUDB2->getCdbXml()
        );
    }

    /**
     * @param EventUpdatedFromUDB2 $eventUpdatedFromUDB2
     * @return JsonDocument
     */
    protected function applyEventUpdatedFromUDB2(
        EventUpdatedFromUDB2 $eventUpdatedFromUDB2
    ) {
        return $this->applyEventCdbXmlFromUDB2(
            $eventUpdatedFromUDB2->getEventId(),
            $eventUpdatedFromUDB2->getCdbXmlNamespaceUri(),
            $eventUpdatedFromUDB2->getCdbXml()
        );
    }

    /**
     * Helper function to save JSONLD document from entryapi cdbxml.
     *
     * @param string $eventId
     * @param string $cdbXmlNamespaceUri
     * @param string $cdbXml
     * @param DomainMessage $domainMessage
     * @return JsonDocument
     */
    protected function applyEventFromCdbXml(
        $eventId,
        $cdbXmlNamespaceUri,
        $cdbXml,
        $domainMessage
    ) {
        $document = $this->newDocument($eventId);

        $eventLd = $this->projectEventCdbXmlToObject(
            $document->getBody(),
            $eventId,
            $cdbXmlNamespaceUri,
            $cdbXml
        );

        // Add creation date and update date from metadata.
        $created = $this->getCreated($domainMessage);
        $eventLd->created = $created;
        $eventLd->modified = $created;

        // Add creator.
        $eventLd->creator = $this->getAuthorFromMetadata($domainMessage->getMetadata())->toNative();

        // Add publisher, which is the consumer name.
        $eventLd->publisher = $this->getConsumerFromMetadata($domainMessage->getMetadata())->toNative();

        return $document->withBody($eventLd);
    }

    /**
     * Helper function to save a JSON-LD document from cdbxml coming from UDB2.
     *
     * @param string $eventId
     * @param string $cdbXmlNamespaceUri
     * @param string $cdbXml
     *
     * @return JsonDocument
     */
    protected function applyEventCdbXmlFromUDB2(
        $eventId,
        $cdbXmlNamespaceUri,
        $cdbXml
    ) {
        $document = $this->newDocument($eventId);

        $eventLd = $this->projectEventCdbXmlToObject(
            $document->getBody(),
            $eventId,
            $cdbXmlNamespaceUri,
            $cdbXml
        );

        return $document->withBody($eventLd);
    }

    /**
     * @param \stdClass $jsonLd
     * @param string $eventId
     * @param string $cdbXmlNamespaceUri
     * @param string $cdbXml
     *
     * @return \stdClass
     */
    protected function projectEventCdbXmlToObject(
        \stdClass $jsonLd,
        $eventId,
        $cdbXmlNamespaceUri,
        $cdbXml
    ) {
        $udb2Event = EventItemFactory::createEventFromCdbXml(
            $cdbXmlNamespaceUri,
            $cdbXml
        );

        $jsonLd = $this->cdbXMLImporter->documentWithCdbXML(
            $jsonLd,
            $udb2Event,
            $this,
            $this,
            $this->slugger
        );

        $this->setMainLanguage($jsonLd, new Language('nl'));

        // Because we can not properly track media coming from UDB2 we simply
        // ignore it and give priority to content added through UDB3.
        // It's possible that an event has been deleted in udb3, but never
        // in udb2. If an update comes for that event from udb2, it should
        // be imported again. This is intended by design.
        // @see https://jira.uitdatabank.be/browse/III-1092
        try {
            $document = $this->loadDocumentFromRepositoryByItemId($eventId);
        } catch (DocumentGoneException $documentGoneException) {
            $document = $this->newDocument($eventId);
        }

        $media = $this->UDB3Media($document);
        if (!empty($media)) {
            $jsonLd->mediaObject = $media;
        }

        // Because UDB2 cannot keep track of UDB3 places as a location
        // ignore it and give priority to content added through UDB3.
        $location = $this->UDB3Location($document);
        if (!empty($location)) {
            $jsonLd->location = $location;
        }

        return $jsonLd;
    }

    /**
     * Return the media of an event if it already exists.
     *
     * @param JsonDocument $document The JsonDocument.
     *
     * @return array
     *  A list of media objects.
     */
    private function UDB3Media($document)
    {
        $media = [];

        if ($document) {
            $item = $document->getBody();
            // At the moment we do not include any media coming from UDB2.
            // If the mediaObject property contains data it's coming from UDB3.
            $item->mediaObject = isset($item->mediaObject) ? $item->mediaObject : [];
        }

        return $media;
    }

    /**
     * Return the location of an event if it already exists.
     *
     * @param JsonDocument $document The JsonDocument.
     *
     * @return array|null
     *  The location
     */
    private function UDB3Location($document)
    {
        $location = null;

        if ($document) {
            $item = $document->getBody();
            $location = isset($item->location) ? $item->location : null;
        }

        return $location;
    }

    /**
     * @param EventCreated $eventCreated
     * @param DomainMessage $domainMessage
     * @return JsonDocument
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

        $this->setMainLanguage($jsonLD, new Language('nl'));

        $jsonLD->name['nl'] = $eventCreated->getTitle();
        $jsonLD->location = array(
                '@type' => 'Place',
            ) + (array)$this->placeJSONLD(
                $eventCreated->getLocation()->getCdbid()
            );

        $calendarJsonLD = $eventCreated->getCalendar()->toJsonLd();
        $jsonLD = (object)array_merge((array)$jsonLD, $calendarJsonLD);

        $availableTo = AvailableTo::createFromCalendar($eventCreated->getCalendar());
        $jsonLD->availableTo = (string)$availableTo;

        // Same as.
        $jsonLD->sameAs = $this->generateSameAs(
            $eventCreated->getEventId(),
            reset($jsonLD->name)
        );

        $eventType = $eventCreated->getEventType();
        $jsonLD->terms = [
            $eventType->toJsonLd(),
        ];

        $theme = $eventCreated->getTheme();
        if (!empty($theme)) {
            $jsonLD->terms[] = $theme->toJsonLd();
        }

        $created = $this->getCreated($domainMessage);
        $jsonLD->created = $created;
        $jsonLD->modified = $created;

        $metaData = $domainMessage->getMetadata()->serialize();
        if (isset($metaData['user_email'])) {
            $jsonLD->creator = $metaData['user_email'];
        } elseif (isset($metaData['user_nick'])) {
            $jsonLD->creator = $metaData['user_nick'];
        }

        $jsonLD->workflowStatus = WorkflowStatus::DRAFT()->getName();

        $defaultAudience = new Audience(AudienceType::EVERYONE());
        $jsonLD->audience = $defaultAudience->serialize();

        return $document->withBody($jsonLD);
    }

    /**
     * @param EventCopied $eventCopied
     * @param DomainMessage $domainMessage
     * @return JsonDocument
     */
    protected function applyEventCopied(
        EventCopied $eventCopied,
        DomainMessage $domainMessage
    ) {
        $originalDocument = $this->repository->get($eventCopied->getOriginalEventId());
        $eventJsonLD = $originalDocument->getBody();

        // Set the created and modified date.
        $created = $this->getCreated($domainMessage);
        $eventJsonLD->created = $created;
        $eventJsonLD->modified = $created;

        // Set the creator.
        $eventJsonLD->creator = $this->getAuthorFromMetadata($domainMessage->getMetadata())->toNative();

        // Set the id.
        $eventJsonLD->{'@id'} = $this->iriGenerator->iri($eventCopied->getItemId());

        // Set the new calendar.
        $eventJsonLD = (object) array_merge(
            (array) $eventJsonLD,
            $eventCopied->getCalendar()->toJsonLd()
        );

        // Set workflow status.
        $eventJsonLD->workflowStatus = WorkflowStatus::DRAFT()->getName();

        // Remove labels.
        unset($eventJsonLD->labels);
        unset($eventJsonLD->hiddenLabels);

        // Set available to and from.
        $availableTo = AvailableTo::createFromCalendar($eventCopied->getCalendar());
        $eventJsonLD->availableTo = (string) $availableTo;
        unset($eventJsonLD->availableFrom);

        $newDocument = new JsonDocument($eventCopied->getItemId());
        $newDocument = $newDocument->withBody($eventJsonLD);
        return $newDocument;
    }

    /**
     * @param EventDeleted $eventDeleted
     * @return null
     */
    protected function applyEventDeleted(EventDeleted $eventDeleted)
    {
        $document = $this->loadDocumentFromRepository($eventDeleted);

        $jsonLD = $document->getBody();

        $jsonLD->workflowStatus = WorkflowStatus::DELETED()->getName();

        return $document->withBody($jsonLD);
    }

    /**
     * Apply the major info updated command to the projector.
     * @param MajorInfoUpdated $majorInfoUpdated
     * @return JsonDocument
     */
    protected function applyMajorInfoUpdated(MajorInfoUpdated $majorInfoUpdated)
    {
        $document = $this
            ->loadDocumentFromRepository($majorInfoUpdated)
            ->apply(OfferUpdate::calendar($majorInfoUpdated->getCalendar()));

        $jsonLD = $document->getBody();

        $jsonLD->name->nl = $majorInfoUpdated->getTitle();
        $jsonLD->location = array(
          '@type' => 'Place',
        ) + (array)$this->placeJSONLD($majorInfoUpdated->getLocation()->getCdbid());

        $availableTo = AvailableTo::createFromCalendar($majorInfoUpdated->getCalendar());
        $jsonLD->availableTo = (string)$availableTo;

        // Remove old theme and event type.
        $jsonLD->terms = array_filter($jsonLD->terms, function ($term) {
            return $term->domain !== EventType::DOMAIN &&  $term->domain !== Theme::DOMAIN;
        });
        $jsonLD->terms = array_values($jsonLD->terms);

        $eventType = $majorInfoUpdated->getEventType();
        $jsonLD->terms[] = $eventType->toJsonLd();

        $theme = $majorInfoUpdated->getTheme();
        if (!empty($theme)) {
            $jsonLD->terms[] = $theme->toJsonLd();
        }

        return $document->withBody($jsonLD);
    }

    /**
     * @param LocationUpdated $locationUpdated
     *
     * @return JsonDocument
     */
    public function applyLocationUpdated(LocationUpdated $locationUpdated)
    {
        $document = $this->loadDocumentFromRepository($locationUpdated);

        $jsonLD = $document->getBody();

        $jsonLD->location = [
            '@type' => 'Place',
         ] + (array) $this->placeJSONLD($locationUpdated->getLocationId()->toNative());

        return $document->withBody($jsonLD);
    }

    /**
     * @param AudienceUpdated $audienceUpdated
     * @return JsonDocument
     */
    protected function applyAudienceUpdated(AudienceUpdated $audienceUpdated)
    {
        $document = $this->loadDocumentFromRepository($audienceUpdated);
        $jsonLD = $document->getBody();

        $jsonLD->audience = $audienceUpdated->getAudience()->serialize();

        return $document->withBody($jsonLD);
    }

    /**
     * @inheritdoc
     */
    public function placeJSONLD($placeId)
    {
        if (empty($placeId)) {
            return array();
        }

        try {
            $placeJSONLD = $this->placeService->getEntity(
                $placeId
            );

            return json_decode($placeJSONLD);
        } catch (EntityNotFoundException $e) {
            // In case the place can not be found at the moment, just add its ID
            return array(
                '@id' => $this->placeService->iri($placeId),
            );
        } catch (DocumentGoneException $e) {
            // In case the place can not be found at the moment, just add its ID
            return array(
                '@id' => $this->placeService->iri($placeId),
            );
        }
    }

    private function generateSameAs($eventId, $name)
    {
        $eventSlug = $this->slugger->slug($name);
        return array(
            'http://www.uitinvlaanderen.be/agenda/e/' . $eventSlug . '/' . $eventId,
        );
    }

    private function getAuthorFromMetadata(Metadata $metadata)
    {
        $properties = $metadata->serialize();

        if (isset($properties['user_nick'])) {
            return new StringLiteral($properties['user_nick']);
        }
    }

    private function getConsumerFromMetadata(Metadata $metadata)
    {
        $properties = $metadata->serialize();

        if (isset($properties['consumer']['name'])) {
            return new StringLiteral($properties['consumer']['name']);
        }
    }

    /**
     * @param DomainMessage $domainMessage
     * @return string
     */
    private function getCreated(DomainMessage $domainMessage)
    {
        $recordedOn = $domainMessage->getRecordedOn()->toString();

        return \DateTime::createFromFormat(
            DateTime::FORMAT_STRING,
            $recordedOn
        )->format('c');
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
    protected function getLabelRemovedClassName()
    {
        return LabelRemoved::class;
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

    /**
     * @return string
     */
    protected function getPriceInfoUpdatedClassName()
    {
        return PriceInfoUpdated::class;
    }

    protected function getContactPointUpdatedClassName()
    {
        return ContactPointUpdated::class;
    }

    protected function getDescriptionUpdatedClassName()
    {
        return DescriptionUpdated::class;
    }

    protected function getCalendarUpdatedClassName()
    {
        return CalendarUpdated::class;
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

    protected function getImagesImportedFromUdb2ClassName()
    {
        return ImagesImportedFromUDB2::class;
    }

    protected function getImagesUpdatedFromUdb2ClassName()
    {
        return ImagesUpdatedFromUDB2::class;
    }

    protected function getTitleUpdatedClassName()
    {
        return TitleUpdated::class;
    }

    protected function getTypeUpdatedClassName()
    {
        return TypeUpdated::class;
    }

    protected function getThemeUpdatedClassName()
    {
        return ThemeUpdated::class;
    }

    protected function getFacilitiesUpdatedClassName()
    {
        return FacilitiesUpdated::class;
    }
}
