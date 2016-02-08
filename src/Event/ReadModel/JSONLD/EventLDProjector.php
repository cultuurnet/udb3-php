<?php

namespace CultuurNet\UDB3\Event\ReadModel\JSONLD;

use Broadway\Domain\DateTime;
use Broadway\Domain\DomainMessage;
use Broadway\Domain\Metadata;
use Broadway\EventHandling\EventListenerInterface;
use CultuurNet\UDB3\Cdb\EventItemFactory;
use CultuurNet\UDB3\CulturefeedSlugger;
use CultuurNet\UDB3\EntityNotFoundException;
use CultuurNet\UDB3\Event\DescriptionTranslated;
use CultuurNet\UDB3\Event\EventEvent;
use CultuurNet\UDB3\Event\Events\BookingInfoUpdated;
use CultuurNet\UDB3\Event\Events\ContactPointUpdated;
use CultuurNet\UDB3\Event\Events\DescriptionUpdated;
use CultuurNet\UDB3\Event\Events\EventCreated;
use CultuurNet\UDB3\Event\Events\EventCreatedFromCdbXml;
use CultuurNet\UDB3\Event\Events\EventDeleted;
use CultuurNet\UDB3\Event\Events\EventImportedFromUDB2;
use CultuurNet\UDB3\Event\Events\EventUpdatedFromCdbXml;
use CultuurNet\UDB3\Event\Events\EventUpdatedFromUDB2;
use CultuurNet\UDB3\Event\Events\LabelAdded;
use CultuurNet\UDB3\Event\Events\ImageAdded;
use CultuurNet\UDB3\Event\Events\ImageRemoved;
use CultuurNet\UDB3\Event\Events\ImageUpdated;
use CultuurNet\UDB3\Event\Events\LabelsMerged;
use CultuurNet\UDB3\Event\Events\MajorInfoUpdated;
use CultuurNet\UDB3\Event\Events\OrganizerDeleted;
use CultuurNet\UDB3\Event\Events\OrganizerUpdated;
use CultuurNet\UDB3\Event\Events\TranslationApplied;
use CultuurNet\UDB3\Event\Events\TranslationDeleted;
use CultuurNet\UDB3\Event\Events\TypicalAgeRangeDeleted;
use CultuurNet\UDB3\Event\Events\TypicalAgeRangeUpdated;
use CultuurNet\UDB3\Event\Events\LabelDeleted;
use CultuurNet\UDB3\Event\EventType;
use CultuurNet\UDB3\Event\ReadModel\DocumentRepositoryInterface;
use CultuurNet\UDB3\Event\ReadModel\JSONLD\CdbXMLImporter;
use CultuurNet\UDB3\Event\ReadModel\JSONLD\OrganizerServiceInterface;
use CultuurNet\UDB3\Event\ReadModel\JSONLD\PlaceServiceInterface;
use CultuurNet\UDB3\Event\TitleTranslated;
use CultuurNet\UDB3\EventHandling\DelegateEventHandlingToSpecificMethodTrait;
use CultuurNet\UDB3\EventServiceInterface;
use CultuurNet\UDB3\Iri\IriGeneratorInterface;
use CultuurNet\UDB3\Label;
use CultuurNet\UDB3\LabelCollection;
use CultuurNet\UDB3\Offer\ReadModel\JSONLD\CdbXMLItemBaseImporter;
use CultuurNet\UDB3\Offer\ReadModel\JSONLD\OfferLDProjector;
use CultuurNet\UDB3\Media\Serialization\MediaObjectSerializer;
use CultuurNet\UDB3\Organizer\OrganizerProjectedToJSONLD;
use CultuurNet\UDB3\OrganizerService;
use CultuurNet\UDB3\Place\PlaceProjectedToJSONLD;
use CultuurNet\UDB3\PlaceService;
use CultuurNet\UDB3\ReadModel\JsonDocument;
use CultuurNet\UDB3\SluggerInterface;
use CultuurNet\UDB3\StringFilter\StringFilterInterface;
use CultuurNet\UDB3\Theme;
use Symfony\Component\Serializer\SerializerInterface;
use ValueObjects\String\String;

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
     * @var SerializerInterface
     */
    protected $mediaObjectSerializer;

    /**
     * @param DocumentRepositoryInterface $repository
     * @param IriGeneratorInterface $iriGenerator
     * @param EventServiceInterface $eventService
     * @param PlaceService $placeService
     * @param OrganizerService $organizerService
     * @param SerializerInterface $mediaObjectSerializer
     */
    public function __construct(
        DocumentRepositoryInterface $repository,
        IriGeneratorInterface $iriGenerator,
        EventServiceInterface $eventService,
        PlaceService $placeService,
        OrganizerService $organizerService,
        SerializerInterface $mediaObjectSerializer
    ) {
        parent::__construct(
            $repository,
            $iriGenerator,
            $organizerService
        );

        $this->placeService = $placeService;
        $this->eventService = $eventService;
        $this->mediaObjectSerializer = $mediaObjectSerializer;

        $this->slugger = new CulturefeedSlugger();
        $this->cdbXMLImporter = new CdbXMLImporter(new CdbXMLItemBaseImporter());
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
        $media = $this->UDB3Media($eventId);
        if (!empty($media)) {
            $jsonLd->mediaObject = $media;
        }

        return $jsonLd;
    }

    /**
     * Return the media of an event if it already exists.
     *
     * @param $eventId
     *  The id of the event.
     *
     * @return array
     *  A list of media objects.
     */
    private function UDB3Media($eventId)
    {
        $document = $this->loadDocumentFromRepositoryByEventId($eventId);
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

                return $jsonLD;
            }
        );
    }

    /**
     * @param EventDeleted $eventDeleted
     */
    protected function applyEventDeleted(EventDeleted $eventDeleted)
    {
        $this->repository->remove($eventDeleted->getEventId());
    }

    /**
     * Apply the major info updated command to the projector.
     */
    protected function applyMajorInfoUpdated(MajorInfoUpdated $majorInfoUpdated)
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

    /**
     * @inheritdoc
     */
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

    /**
     * @inheritdoc
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

    /**
     * @param LabelsMerged $labelsMerged
     */
    protected function applyLabelsMerged(LabelsMerged $labelsMerged)
    {
        $document = $this->loadDocumentFromRepositoryByEventId($labelsMerged->getEventId()->toNative());

        $eventLd = $document->getBody();

        $labels = isset($eventLd->labels) ? $eventLd->labels : [];

        $currentCollection = LabelCollection::fromStrings($labels);

        $newLabels = $labelsMerged->getLabels();

        $eventLd->labels = $currentCollection->merge($newLabels)->toStrings();

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

    protected function applyTranslationApplied(
        TranslationApplied $translationApplied
    ) {
        $document = $this->loadDocumentFromRepositoryByEventId($translationApplied->getEventId()->toNative());

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
        $document = $this->loadDocumentFromRepositoryByEventId($translationDeleted->getEventId()->toNative());

        $eventLd = $document->getBody();

        unset($eventLd->name->{$translationDeleted->getLanguage()->getCode()});

        unset($eventLd->description->{$translationDeleted->getLanguage()->getCode()});

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
    protected function applyTypicalAgeRangeUpdated(
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
    protected function applyTypicalAgeRangeDeleted(
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
    protected function applyOrganizerUpdated(OrganizerUpdated $organizerUpdated)
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
    protected function applyOrganizerDeleted(OrganizerDeleted $organizerDeleted)
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
    protected function applyContactPointUpdated(ContactPointUpdated $contactPointUpdated)
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
    protected function applyBookingInfoUpdated(BookingInfoUpdated $bookingInfoUpdated)
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
    protected function applyImageAdded(ImageAdded $imageAdded)
    {
        $document = $this->loadDocumentFromRepository($imageAdded);

        $eventLd = $document->getBody();
        $eventLd->mediaObject = isset($eventLd->mediaObject) ? $eventLd->mediaObject : [];

        $imageData = $this->mediaObjectSerializer
            ->serialize($imageAdded->getImage(), 'json-ld');
        $eventLd->mediaObject[] = $imageData;

        $this->repository->save($document->withBody($eventLd));
    }

    /**
     * Apply the ImageUpdated event to the event repository.
     *
     * @param ImageUpdated $imageUpdated
     */
    protected function applyImageUpdated(ImageUpdated $imageUpdated)
    {
        $document = $this->loadDocumentFromRepositoryByEventId($imageUpdated->getItemId());

        $eventLd = $document->getBody();

        if (!isset($eventLd->mediaObject)) {
            throw new \Exception('The image to update could not be found.');
        }

        $updatedMediaObjects = [];

        foreach ($eventLd->mediaObject as $mediaObject) {
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

        $this->repository->save($document->withBody($eventLd));
    }

    /**
     * @param ImageRemoved $imageRemoved
     */
    protected function applyImageRemoved(ImageRemoved $imageRemoved)
    {
        $document = $this->loadDocumentFromRepositoryByEventId(
            $imageRemoved->getItemId()
        );

        $eventLd = $document->getBody();

        // Nothing to remove if there are no media objects!
        if (!isset($eventLd->mediaObject)) {
            return;
        }

        $imageId = (string) $imageRemoved->getImage()->getMediaObjectId();

        /**
         * Matches any object that is not the removed image.
         *
         * @param Object $mediaObject
         *  An existing projection of a media object.
         *
         * @return bool
         *  Returns true when the media object does not match the image to remove.
         */
        $shouldNotBeRemoved = function ($mediaObject) use ($imageId) {
            $containsId = !!strpos($mediaObject->{'@id'}, $imageId);
            return !$containsId;
        };

        // Remove any media objects that match the image.
        $filteredMediaObjects = array_filter(
            $eventLd->mediaObject,
            $shouldNotBeRemoved
        );

        // Unset the main image if it matches the removed image
        if (isset($eventLd->image) && strpos($eventLd->{'image'}, $imageId)) {
            unset($eventLd->{"image"});
        }

        // If no media objects are left remove the attribute.
        if (empty($filteredMediaObjects)) {
            unset($eventLd->{"mediaObject"});
        } else {
            $eventLd->mediaObject = array_values($filteredMediaObjects);
        }

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
}
