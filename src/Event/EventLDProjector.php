<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Event;

use Broadway\Domain\DateTime;
use Broadway\Domain\DomainMessage;
use Broadway\Domain\Metadata;
use Broadway\EventHandling\EventListenerInterface;
use CultuurNet\UDB3\Cdb\EventItemFactory;
use CultuurNet\UDB3\CulturefeedSlugger;
use CultuurNet\UDB3\EntityNotFoundException;
use CultuurNet\UDB3\Event\Events\EventCreatedFromCdbXml;
use CultuurNet\UDB3\Event\Events\BookingInfoUpdated;
use CultuurNet\UDB3\Event\Events\ContactPointUpdated;
use CultuurNet\UDB3\Event\Events\DescriptionUpdated;
use CultuurNet\UDB3\Event\Events\EventCreated;
use CultuurNet\UDB3\Event\Events\EventDeleted;
use CultuurNet\UDB3\Event\Events\EventImportedFromUDB2;
use CultuurNet\UDB3\Event\Events\EventUpdatedFromCdbXml;
use CultuurNet\UDB3\Event\Events\EventUpdatedFromUDB2;
use CultuurNet\UDB3\Event\Events\EventWasLabelled;
use CultuurNet\UDB3\Event\Events\ImageAdded;
use CultuurNet\UDB3\Event\Events\ImageDeleted;
use CultuurNet\UDB3\Event\Events\ImageUpdated;
use CultuurNet\UDB3\Event\Events\LabelsMerged;
use CultuurNet\UDB3\Event\Events\MajorInfoUpdated;
use CultuurNet\UDB3\Event\Events\OrganizerDeleted;
use CultuurNet\UDB3\Event\Events\OrganizerUpdated;
use CultuurNet\UDB3\Event\Events\TranslationApplied;
use CultuurNet\UDB3\Event\Events\TranslationDeleted;
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
use CultuurNet\UDB3\LabelCollection;
use CultuurNet\UDB3\Offer\ReadModel\JSONLD\CdbXMLItemBaseImporter;
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
        $this->repository = $repository;
        $this->iriGenerator = $iriGenerator;
        $this->organizerService = $organizerService;
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
        $this->applyEventCdbXml(
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
        $this->applyEventCdbXml(
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

        // Because we can not properly track media coming from UDB2 we simply
        // ignore it and give priority to content added through UDB3.
        $media = $this->UDB3Media($eventId);
        if (!empty($media)) {
            $eventLd->mediaObject = $media;
        }

        $this->repository->save($document->withBody($eventLd));
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

        // Because we can not properly track media coming from UDB2 we simply
        // ignore it and give priority to content added through UDB3.
        $media = $this->UDB3Media($eventId);
        if (!empty($media)) {
            $eventLd->mediaObject = $media;
        }

        $this->repository->save($document->withBody($eventLd));
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
     * @param EventWasLabelled $eventWasLabelled
     */
    protected function applyEventWasLabelled(EventWasLabelled $eventWasLabelled)
    {
        $document = $this->loadDocumentFromRepository($eventWasLabelled);

        $eventLd = $document->getBody();

        $labels = isset($eventLd->labels) ? $eventLd->labels : [];
        $label = (string) $eventWasLabelled->getLabel();

        $labels[] = $label;
        $eventLd->labels = array_unique($labels);

        $this->repository->save($document->withBody($eventLd));
    }

    protected function applyUnlabelled(Unlabelled $unlabelled)
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

        $document = $this->loadDocumentFromRepository($imageUpdated);

        $eventLd = $document->getBody();
        $eventLd->mediaObject = isset($eventLd->mediaObject) ? $eventLd->mediaObject : [];
        $imageData = $this->mediaObjectSerializer->serialize(
            $imageUpdated->getMediaObject(),
            'json-ld'
        );
        $eventLd->mediaObject[$imageUpdated->getIndexToUpdate()] = $imageData;

        $this->repository->save($document->withBody($eventLd));

    }

    /**
     * Apply the imageDeleted event to the event repository.
     *
     * @param ImageDeleted $imageDeleted
     */
    protected function applyImageDeleted(ImageDeleted $imageDeleted)
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
}
