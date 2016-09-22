<?php

namespace CultuurNet\UDB3\Event\ReadModel\JSONLD;

use Broadway\Domain\DateTime;
use Broadway\Domain\DomainMessage;
use Broadway\Domain\Metadata;
use Broadway\EventHandling\EventListenerInterface;
use CultuurNet\UDB3\Cdb\EventItemFactory;
use CultuurNet\UDB3\CulturefeedSlugger;
use CultuurNet\UDB3\EntityNotFoundException;
use CultuurNet\UDB3\Event\Events\BookingInfoUpdated;
use CultuurNet\UDB3\Event\Events\ContactPointUpdated;
use CultuurNet\UDB3\Event\Events\DescriptionTranslated;
use CultuurNet\UDB3\Event\Events\DescriptionUpdated;
use CultuurNet\UDB3\Event\Events\EventCreated;
use CultuurNet\UDB3\Event\Events\EventCreatedFromCdbXml;
use CultuurNet\UDB3\Event\Events\EventDeleted;
use CultuurNet\UDB3\Event\Events\EventImportedFromUDB2;
use CultuurNet\UDB3\Event\Events\EventUpdatedFromCdbXml;
use CultuurNet\UDB3\Event\Events\EventUpdatedFromUDB2;
use CultuurNet\UDB3\Event\Events\ImageAdded;
use CultuurNet\UDB3\Event\Events\ImageRemoved;
use CultuurNet\UDB3\Event\Events\ImageUpdated;
use CultuurNet\UDB3\Event\Events\LabelAdded;
use CultuurNet\UDB3\Event\Events\LabelDeleted;
use CultuurNet\UDB3\Event\Events\LabelsMerged;
use CultuurNet\UDB3\Event\Events\MainImageSelected;
use CultuurNet\UDB3\Event\Events\MajorInfoUpdated;
use CultuurNet\UDB3\Event\Events\Moderation\Approved;
use CultuurNet\UDB3\Event\Events\Moderation\FlaggedAsDuplicate;
use CultuurNet\UDB3\Event\Events\Moderation\FlaggedAsInappropriate;
use CultuurNet\UDB3\Event\Events\Moderation\Published;
use CultuurNet\UDB3\Event\Events\Moderation\Rejected;
use CultuurNet\UDB3\Event\Events\OrganizerDeleted;
use CultuurNet\UDB3\Event\Events\OrganizerUpdated;
use CultuurNet\UDB3\Event\Events\TitleTranslated;
use CultuurNet\UDB3\Event\Events\TranslationApplied;
use CultuurNet\UDB3\Event\Events\TranslationDeleted;
use CultuurNet\UDB3\Event\Events\TypicalAgeRangeDeleted;
use CultuurNet\UDB3\Event\Events\TypicalAgeRangeUpdated;
use CultuurNet\UDB3\Event\EventType;
use CultuurNet\UDB3\Event\ReadModel\DocumentGoneException;
use CultuurNet\UDB3\Event\ReadModel\DocumentRepositoryInterface;
use CultuurNet\UDB3\Event\EventServiceInterface;
use CultuurNet\UDB3\Iri\IriGeneratorInterface;
use CultuurNet\UDB3\LabelCollection;
use CultuurNet\UDB3\Offer\IriOfferIdentifierFactoryInterface;
use CultuurNet\UDB3\Offer\ReadModel\JSONLD\CdbXMLItemBaseImporter;
use CultuurNet\UDB3\Offer\ReadModel\JSONLD\OfferLDProjector;
use CultuurNet\UDB3\Offer\ReadModel\JSONLD\OfferUpdate;
use CultuurNet\UDB3\Offer\WorkflowStatus;
use CultuurNet\UDB3\Organizer\OrganizerProjectedToJSONLD;
use CultuurNet\UDB3\OrganizerService;
use CultuurNet\UDB3\Place\Events\PlaceProjectedToJSONLD;
use CultuurNet\UDB3\PlaceService;
use CultuurNet\UDB3\ReadModel\JsonDocument;
use CultuurNet\UDB3\StringFilter\StringFilterInterface;
use CultuurNet\UDB3\Theme;
use Symfony\Component\Serializer\SerializerInterface;
use ValueObjects\String\String;
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
     * @param DocumentRepositoryInterface $repository
     * @param IriGeneratorInterface $iriGenerator
     * @param EventServiceInterface $eventService
     * @param PlaceService $placeService
     * @param OrganizerService $organizerService
     * @param SerializerInterface $mediaObjectSerializer
     * @param IriOfferIdentifierFactoryInterface $iriOfferIdentifierFactory
     */
    public function __construct(
        DocumentRepositoryInterface $repository,
        IriGeneratorInterface $iriGenerator,
        EventServiceInterface $eventService,
        PlaceService $placeService,
        OrganizerService $organizerService,
        SerializerInterface $mediaObjectSerializer,
        IriOfferIdentifierFactoryInterface $iriOfferIdentifierFactory
    ) {
        parent::__construct(
            $repository,
            $iriGenerator,
            $organizerService,
            $mediaObjectSerializer
        );

        $this->placeService = $placeService;
        $this->eventService = $eventService;

        $this->slugger = new CulturefeedSlugger();
        $this->cdbXMLImporter = new CdbXMLImporter(new CdbXMLItemBaseImporter());

        $this->iriOfferIdentifierFactory = $iriOfferIdentifierFactory;
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
            $document = $this->loadDocumentFromRepositoryByItemId(
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
        $identifier = $this->iriOfferIdentifierFactory->fromIri(
            Url::fromNative($placeProjectedToJSONLD->getIri())
        );

        $eventsLocatedAtPlace = $this->eventsLocatedAtPlace(
            $identifier->getId()
        );

        $placeJSONLD = $this->placeService->getEntity(
            $identifier->getId()
        );

        foreach ($eventsLocatedAtPlace as $eventId) {
            $document = $this->loadDocumentFromRepositoryByItemId(
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
    protected function applyEventImportedFromUDB2(
        EventImportedFromUDB2 $eventImportedFromUDB2
    ) {
        $this->applyEventCdbXmlFromUDB2(
            $eventImportedFromUDB2->getEventId(),
            $eventImportedFromUDB2->getCdbXmlNamespaceUri(),
            $eventImportedFromUDB2->getCdbXml()
        );
    }

    /**
     * @param EventUpdatedFromUDB2 $eventUpdatedFromUDB2
     */
    protected function applyEventUpdatedFromUDB2(
        EventUpdatedFromUDB2 $eventUpdatedFromUDB2
    ) {
        $this->applyEventCdbXmlFromUDB2(
            $eventUpdatedFromUDB2->getEventId(),
            $eventUpdatedFromUDB2->getCdbXmlNamespaceUri(),
            $eventUpdatedFromUDB2->getCdbXml()
        );
    }

    /**
     * @param EventCreatedFromCdbXml $eventCreatedFromCdbXml
     * @param DomainMessage $domainMessage
     */
    protected function applyEventCreatedFromCdbXml(
        EventCreatedFromCdbXml $eventCreatedFromCdbXml,
        DomainMessage $domainMessage
    ) {
        $cdbXmlNamespaceUri = $eventCreatedFromCdbXml->getCdbXmlNamespaceUri()->toNative();
        $cdbXml = $eventCreatedFromCdbXml->getEventXmlString()->toEventXmlString();
        $eventId = $eventCreatedFromCdbXml->getEventId()->toNative();

        $this->applyEventFromCdbXml(
            $eventId,
            $cdbXmlNamespaceUri,
            $cdbXml,
            $domainMessage
        );
    }

    /**
     * @param EventUpdatedFromCdbXml $eventUpdatedFromCdbXml
     * @param DomainMessage $domainMessage
     */
    protected function applyEventUpdatedFromCdbXml(
        EventUpdatedFromCdbXml $eventUpdatedFromCdbXml,
        DomainMessage $domainMessage
    ) {
        $cdbXmlNamespaceUri = $eventUpdatedFromCdbXml->getCdbXmlNamespaceUri()->toNative();
        $cdbXml = $eventUpdatedFromCdbXml->getEventXmlString()->toEventXmlString();
        $eventId = $eventUpdatedFromCdbXml->getEventId()->toNative();

        $this->applyEventFromCdbXml(
            $eventId,
            $cdbXmlNamespaceUri,
            $cdbXml,
            $domainMessage
        );
    }

    /**
     * Helper function to save JSONLD document from entryapi cdbxml.
     *
     * @param string $eventId
     * @param string $cdbXmlNamespaceUri
     * @param string $cdbXml
     * @param DomainMessage $domainMessage
     */
    protected function applyEventFromCdbXml(
        $eventId,
        $cdbXmlNamespaceUri,
        $cdbXml,
        $domainMessage
    ) {
        $this->saveNewDocument(
            $eventId,
            function (\stdClass $eventLd) use ($eventId, $cdbXmlNamespaceUri, $cdbXml, $domainMessage) {
                $eventLd = $this->projectEventCdbXmlToObject(
                    $eventLd,
                    $eventId,
                    $cdbXmlNamespaceUri,
                    $cdbXml
                );

                // Add creation date and update date from metadata.
                $eventCreationDate = $domainMessage->getRecordedOn();

                $eventCreationString = $eventCreationDate->toString();
                $eventCreationDateTime = \DateTime::createFromFormat(
                    DateTime::FORMAT_STRING,
                    $eventCreationString
                );
                $eventLd->created = $eventCreationDateTime->format('c');
                $eventLd->modified = $eventCreationDateTime->format('c');

                // Add creator.
                $eventLd->creator = $this->getAuthorFromMetadata($domainMessage->getMetadata())->toNative();

                // Add publisher, which is the consumer name.
                $eventLd->publisher = $this->getConsumerFromMetadata($domainMessage->getMetadata())->toNative();

                return $eventLd;
            }
        );
    }

    /**
     * @param string $eventId
     * @param callable $fn
     */
    protected function saveNewDocument($eventId, callable $fn)
    {
        $document = $this
            ->newDocument($eventId)
            ->apply($fn);

        $this->repository->save($document);
    }

    /**
     * Helper function to save a JSON-LD document from cdbxml coming from UDB2.
     *
     * @param string $eventId
     * @param string $cdbXmlNamespaceUri
     * @param string $cdbXml
     */
    protected function applyEventCdbXmlFromUDB2(
        $eventId,
        $cdbXmlNamespaceUri,
        $cdbXml
    ) {
        $this->saveNewDocument(
            $eventId,
            function (\stdClass $eventLd) use ($cdbXmlNamespaceUri, $eventId, $cdbXml) {
                return $this->projectEventCdbXmlToObject(
                    $eventLd,
                    $eventId,
                    $cdbXmlNamespaceUri,
                    $cdbXml
                ) ;
            }
        );
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
     */
    protected function applyEventCreated(
        EventCreated $eventCreated,
        DomainMessage $domainMessage
    ) {
        $this->saveNewDocument(
            $eventCreated->getEventId(),
            function (\stdClass $jsonLD) use ($eventCreated, $domainMessage) {
                $jsonLD->{'@id'} = $this->iriGenerator->iri(
                    $eventCreated->getEventId()
                );
                $jsonLD->name['nl'] = $eventCreated->getTitle();
                $jsonLD->location = array(
                        '@type' => 'Place',
                    ) + (array)$this->placeJSONLD(
                        $eventCreated->getLocation()->getCdbid()
                    );

                $calendarJsonLD = $eventCreated->getCalendar()->toJsonLd();
                $jsonLD = (object)array_merge((array)$jsonLD, $calendarJsonLD);

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
                $jsonLD->modified = $jsonLD->created;

                $metaData = $domainMessage->getMetadata()->serialize();
                if (isset($metaData['user_email'])) {
                    $jsonLD->creator = $metaData['user_email'];
                } elseif (isset($metaData['user_nick'])) {
                    $jsonLD->creator = $metaData['user_nick'];
                }

                $jsonLD->workflowStatus = WorkflowStatus::DRAFT()->getName();

                return $jsonLD;
            }
        );
    }

    /**
     * @param EventDeleted $eventDeleted
     */
    protected function applyEventDeleted(EventDeleted $eventDeleted)
    {
        $this->repository->remove($eventDeleted->getItemId());
    }

    /**
     * Apply the major info updated command to the projector.
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
                '@id' => $this->placeService->iri($placeId)
            );
        } catch (DocumentGoneException $e) {
            // In case the place can not be found at the moment, just add its ID
            return array(
                '@id' => $this->placeService->iri($placeId)
            );
        }
    }

    /**
     * @param LabelsMerged $labelsMerged
     */
    protected function applyLabelsMerged(LabelsMerged $labelsMerged)
    {
        $document = $this->loadDocumentFromRepositoryByItemId($labelsMerged->getEventId()->toNative());

        $eventLd = $document->getBody();

        $labels = isset($eventLd->labels) ? $eventLd->labels : [];

        $currentCollection = LabelCollection::fromStrings($labels);

        $newLabels = $labelsMerged->getLabels();

        $eventLd->labels = $currentCollection->merge($newLabels)->toStrings();

        $this->repository->save($document->withBody($eventLd));
    }

    protected function applyTranslationApplied(
        TranslationApplied $translationApplied
    ) {
        $document = $this->loadDocumentFromRepositoryByItemId($translationApplied->getEventId()->toNative());

        $eventLd = $document->getBody();

        if ($translationApplied->getTitle() !== null) {
            $eventLd->name->{$translationApplied->getLanguage()->getCode(
            )} = $translationApplied->getTitle()->toNative();
        }

        if ($translationApplied->getLongDescription() !== null) {
            $eventLd->description->{$translationApplied->getLanguage()->getCode(
            )} = $translationApplied->getLongDescription()->toNative();
        }

        $this->repository->save($document->withBody($eventLd));
    }

    /**
     * Apply the translation deleted event to the event repository.
     * @param TranslationDeleted $translationDeleted
     */
    protected function applyTranslationDeleted(
        TranslationDeleted $translationDeleted
    ) {
        $document = $this->loadDocumentFromRepositoryByItemId($translationDeleted->getEventId()->toNative());

        $eventLd = $document->getBody();

        unset($eventLd->name->{$translationDeleted->getLanguage()->getCode()});

        unset($eventLd->description->{$translationDeleted->getLanguage()->getCode()});

        $this->repository->save($document->withBody($eventLd));
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

    private function getAuthorFromMetadata(Metadata $metadata)
    {
        $properties = $metadata->serialize();

        if (isset($properties['user_nick'])) {
            return new String($properties['user_nick']);
        }
    }

    private function getConsumerFromMetadata(Metadata $metadata)
    {
        $properties = $metadata->serialize();

        if (isset($properties['consumer']['name'])) {
            return new String($properties['consumer']['name']);
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
