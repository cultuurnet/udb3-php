<?php

namespace CultuurNet\UDB3\Event;

use Broadway\EventSourcing\EventSourcedAggregateRoot;
use CultuurNet\UDB3\BookingInfo;
use CultuurNet\UDB3\Calendar;
use CultuurNet\UDB3\CalendarInterface;
use CultuurNet\UDB3\Cdb\EventItemFactory;
use CultuurNet\UDB3\Cdb\UpdateableWithCdbXmlInterface;
use CultuurNet\UDB3\ContactPoint;
use CultuurNet\UDB3\Event\Events\AudienceUpdated;
use CultuurNet\UDB3\Event\Events\BookingInfoUpdated;
use CultuurNet\UDB3\Event\Events\CalendarUpdated;
use CultuurNet\UDB3\Event\Events\Concluded;
use CultuurNet\UDB3\Event\Events\ContactPointUpdated;
use CultuurNet\UDB3\Event\Events\DescriptionTranslated;
use CultuurNet\UDB3\Event\Events\DescriptionUpdated;
use CultuurNet\UDB3\Event\Events\EventCdbXMLInterface;
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
use CultuurNet\UDB3\Event\Events\TitleTranslated;
use CultuurNet\UDB3\Event\Events\TitleUpdated;
use CultuurNet\UDB3\Event\Events\TypicalAgeRangeDeleted;
use CultuurNet\UDB3\Event\Events\TypicalAgeRangeUpdated;
use CultuurNet\UDB3\Event\ValueObjects\Audience;
use CultuurNet\UDB3\Label;
use CultuurNet\UDB3\LabelCollection;
use CultuurNet\UDB3\Language;
use CultuurNet\UDB3\Location\Location;
use CultuurNet\UDB3\Location\LocationId;
use CultuurNet\UDB3\Media\ImageCollection;
use CultuurNet\UDB3\Media\Image;
use CultuurNet\UDB3\Offer\AbstractCalendarUpdated;
use CultuurNet\UDB3\Offer\Commands\Image\AbstractUpdateImage;
use CultuurNet\UDB3\Offer\Offer;
use CultuurNet\UDB3\Offer\WorkflowStatus;
use CultuurNet\UDB3\PriceInfo\PriceInfo;
use CultuurNet\UDB3\Theme;
use CultuurNet\UDB3\Title;
use ValueObjects\Identity\UUID;
use ValueObjects\StringLiteral\StringLiteral;

class Event extends Offer implements UpdateableWithCdbXmlInterface
{
    /**
     * @var string
     */
    protected $eventId;

    /**
     * @var Audience
     */
    private $audience;

    /**
     * @var boolean
     */
    private $concluded = false;

    const MAIN_LANGUAGE_CODE = 'nl';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Factory method to create a new event.
     *
     * @param $eventId
     * @param Title $title
     * @param EventType $eventType
     * @param Location $location
     * @param CalendarInterface $calendar
     * @param Theme|null $theme
     * @param \DateTimeImmutable|null $publicationDate
     * @return Event
     */
    public static function create(
        $eventId,
        Title $title,
        EventType $eventType,
        Location $location,
        CalendarInterface $calendar,
        Theme $theme = null,
        \DateTimeImmutable $publicationDate = null
    ) {
        $event = new self();

        $event->apply(
            new EventCreated(
                $eventId,
                $title,
                $eventType,
                $location,
                $calendar,
                $theme,
                $publicationDate
            )
        );

        return $event;
    }

    /**
     * @param string $newEventId
     * @param CalendarInterface $calendar
     *
     * @return Event
     */
    public function copy($newEventId, CalendarInterface $calendar)
    {
        if ($this->hasUncommittedEvents()) {
            throw new \RuntimeException('I refuse to copy, there are uncommitted events present.');
        }

        // The copied event will have a playhead of the original event + 1
        $copy = clone $this;

        $copy->apply(
            new EventCopied(
                $newEventId,
                $this->eventId,
                $calendar
            )
        );

        return $copy;
    }

    /**
     * @param string $eventId
     * @param string $cdbXml
     * @param string $cdbXmlNamespaceUri
     * @return Event
     */
    public static function importFromUDB2(
        $eventId,
        $cdbXml,
        $cdbXmlNamespaceUri
    ) {
        $event = new self();
        $event->apply(
            new EventImportedFromUDB2(
                $eventId,
                $cdbXml,
                $cdbXmlNamespaceUri
            )
        );

        return $event;
    }

    /**
     * @param ImageCollection $images
     */
    public function updateImagesFromUDB2(ImageCollection $images)
    {
        $this->apply(new ImagesUpdatedFromUDB2($this->eventId, $images));
    }

    /**
     * @param ImageCollection $images
     */
    public function importImagesFromUDB2(ImageCollection $images)
    {
        $this->apply(new ImagesImportedFromUDB2($this->eventId, $images));
    }

    /**
     * {@inheritdoc}
     */
    public function getAggregateRootId()
    {
        return $this->eventId;
    }

    /**
     * @return UUID[]
     */
    public function getMediaObjects()
    {
        return $this->mediaObjects;
    }

    protected function applyEventCreated(EventCreated $eventCreated)
    {
        $this->eventId = $eventCreated->getEventId();
        $this->workflowStatus = WorkflowStatus::DRAFT();
    }

    /**
     * @param EventCopied $eventCopied
     */
    protected function applyEventCopied(EventCopied $eventCopied)
    {
        $this->eventId = $eventCopied->getItemId();
        $this->workflowStatus = WorkflowStatus::DRAFT();
        $this->labels = new LabelCollection();
    }

    protected function applyEventImportedFromUDB2(
        EventImportedFromUDB2 $eventImported
    ) {
        $this->eventId = $eventImported->getEventId();
        $this->setUDB2Data($eventImported);
    }

    /**
     * @param EventUpdatedFromUDB2 $eventUpdated
     */
    protected function applyEventUpdatedFromUDB2(
        EventUpdatedFromUDB2 $eventUpdated
    ) {
        $this->setUDB2Data($eventUpdated);
    }

    /**
     * @param EventCdbXMLInterface $eventCdbXML
     */
    protected function setUDB2Data(
        EventCdbXMLInterface $eventCdbXML
    ) {
        $udb2Event = EventItemFactory::createEventFromCdbXml(
            $eventCdbXML->getCdbXmlNamespaceUri(),
            $eventCdbXML->getCdbXml()
        );

        $this->importWorkflowStatus($udb2Event);
        $this->labels = LabelCollection::fromKeywords($udb2Event->getKeywords(true));
    }

    /**
     * Update the major info.
     *
     * @param Title $title
     * @param EventType $eventType
     * @param Location $location
     * @param CalendarInterface $calendar
     * @param Theme|null $theme
     */
    public function updateMajorInfo(
        Title $title,
        EventType $eventType,
        Location $location,
        CalendarInterface $calendar,
        Theme $theme = null
    ) {
        $this->apply(new MajorInfoUpdated($this->eventId, $title, $eventType, $location, $calendar, $theme));
    }

    /**
     * @param LocationId $locationId
     */
    public function updateLocation(LocationId $locationId)
    {
        // For now no special business rules for updating the location of an event.
        $this->apply(new LocationUpdated($this->eventId, $locationId));
    }

    /**
     * @param Audience $audience
     */
    public function updateAudience(
        Audience $audience
    ) {
        if (is_null($this->audience) || !$this->audience->equals($audience)) {
            $this->apply(new AudienceUpdated(
                $this->eventId,
                $audience
            ));
        }
    }

    /**
     * @param AudienceUpdated $audienceUpdated
     */
    public function applyAudienceUpdated(AudienceUpdated $audienceUpdated)
    {
        $this->audience= $audienceUpdated->getAudience();
    }

    /**
     * @inheritDoc
     * @return ImagesImportedFromUDB2
     */
    protected function createImagesImportedFromUDB2(ImageCollection $images)
    {
        return new ImagesImportedFromUDB2($this->eventId, $images);
    }

    /**
     * @inheritDoc
     * @return ImagesUpdatedFromUDB2
     */
    protected function createImagesUpdatedFromUDB2(ImageCollection $images)
    {
        return new ImagesUpdatedFromUDB2($this->eventId, $images);
    }

    /**
     * @inheritdoc
     */
    public function updateWithCdbXml($cdbXml, $cdbXmlNamespaceUri)
    {
        $this->apply(
            new EventUpdatedFromUDB2(
                $this->eventId,
                $cdbXml,
                $cdbXmlNamespaceUri
            )
        );
    }

    /**
     * @param Label $label
     * @return LabelAdded
     */
    protected function createLabelAddedEvent(Label $label)
    {
        return new LabelAdded($this->eventId, $label);
    }

    /**
     * @param Label $label
     * @return LabelRemoved
     */
    protected function createLabelRemovedEvent(Label $label)
    {
        return new LabelRemoved($this->eventId, $label);
    }

    /**
     * @param Image $image
     * @return ImageAdded
     */
    protected function createImageAddedEvent(Image $image)
    {
        return new ImageAdded($this->eventId, $image);
    }

    /**
     * @param Image $image
     * @return ImageRemoved
     */
    protected function createImageRemovedEvent(Image $image)
    {
        return new ImageRemoved($this->eventId, $image);
    }

    /**
     * @param AbstractUpdateImage $updateImageCommand
     * @return ImageUpdated
     */
    protected function createImageUpdatedEvent(
        AbstractUpdateImage $updateImageCommand
    ) {
        return new ImageUpdated(
            $this->eventId,
            $updateImageCommand->getMediaObjectId(),
            $updateImageCommand->getDescription(),
            $updateImageCommand->getCopyrightHolder()
        );
    }

    /**
     * @param Image $image
     * @return MainImageSelected
     */
    protected function createMainImageSelectedEvent(Image $image)
    {
        return new MainImageSelected($this->eventId, $image);
    }

    /**
     * @param Language $language
     * @param StringLiteral $title
     * @return TitleTranslated
     */
    protected function createTitleTranslatedEvent(Language $language, StringLiteral $title)
    {
        return new TitleTranslated($this->eventId, $language, $title);
    }

    /**
     * @param Title $title
     * @return TitleUpdated
     */
    protected function createTitleUpdatedEvent(Title $title)
    {
        return new TitleUpdated($this->eventId, $title);
    }

    /**
     * @param Language $language
     * @param StringLiteral $description
     * @return DescriptionTranslated
     */
    protected function createDescriptionTranslatedEvent(Language $language, StringLiteral $description)
    {
        return new DescriptionTranslated($this->eventId, $language, $description);
    }

    /**
     * @param string $description
     * @return DescriptionUpdated
     */
    protected function createDescriptionUpdatedEvent($description)
    {
        return new DescriptionUpdated($this->eventId, $description);
    }

    /**
     * @inheritdoc
     */
    protected function createCalendarUpdatedEvent(Calendar $calendar)
    {
        return new CalendarUpdated($this->eventId, $calendar);
    }

    /**
     * @param string $typicalAgeRange
     * @return TypicalAgeRangeUpdated
     */
    protected function createTypicalAgeRangeUpdatedEvent($typicalAgeRange)
    {
        return new TypicalAgeRangeUpdated($this->eventId, $typicalAgeRange);
    }

    /**
     * @return TypicalAgeRangeDeleted
     */
    protected function createTypicalAgeRangeDeletedEvent()
    {
        return new TypicalAgeRangeDeleted($this->eventId);
    }

    /**
     * @param string $organizerId
     * @return OrganizerUpdated
     */
    protected function createOrganizerUpdatedEvent($organizerId)
    {
        return new OrganizerUpdated($this->eventId, $organizerId);
    }

    /**
     * @param string $organizerId
     * @return OrganizerDeleted
     */
    protected function createOrganizerDeletedEvent($organizerId)
    {
        return new OrganizerDeleted($this->eventId, $organizerId);
    }

    /**
     * @param ContactPoint $contactPoint
     * @return ContactPointUpdated
     */
    protected function createContactPointUpdatedEvent(ContactPoint $contactPoint)
    {
        return new ContactPointUpdated($this->eventId, $contactPoint);
    }

    /**
     * @param BookingInfo $bookingInfo
     * @return BookingInfoUpdated
     */
    protected function createBookingInfoUpdatedEvent(BookingInfo $bookingInfo)
    {
        return new BookingInfoUpdated($this->eventId, $bookingInfo);
    }

    /**
     * @param PriceInfo $priceInfo
     * @return PriceInfoUpdated
     */
    protected function createPriceInfoUpdatedEvent(PriceInfo $priceInfo)
    {
        return new PriceInfoUpdated($this->eventId, $priceInfo);
    }

    /**
     * @return EventDeleted
     */
    protected function createOfferDeletedEvent()
    {
        return new EventDeleted($this->eventId);
    }

    /**
     * @inheritdoc
     */
    protected function createPublishedEvent(\DateTimeInterface $publicationDate)
    {
        return new Published($this->eventId, $publicationDate);
    }

    /**
     * @inheritdoc
     */
    protected function createApprovedEvent()
    {
        return new Approved($this->eventId);
    }

    /**
     * @inheritdoc
     */
    protected function createRejectedEvent(StringLiteral $reason)
    {
        return new Rejected($this->eventId, $reason);
    }

    /**
     * @inheritDoc
     */
    protected function createFlaggedAsDuplicate()
    {
        return new FlaggedAsDuplicate($this->eventId);
    }

    /**
     * @inheritDoc
     */
    protected function createFlaggedAsInappropriate()
    {
        return new FlaggedAsInappropriate($this->eventId);
    }

    /**
     * Use reflection to get check if the aggregate has uncommitted events.
     * @return bool
     */
    private function hasUncommittedEvents()
    {
        $reflector = new \ReflectionClass(EventSourcedAggregateRoot::class);
        $property = $reflector->getProperty('uncommittedEvents');

        $property->setAccessible(true);
        $uncommittedEvents = $property->getValue($this);

        return !empty($uncommittedEvents);
    }

    public function conclude()
    {
        if (!$this->concluded) {
            $this->apply(new Concluded($this->eventId));
        }
    }

    /**
     * @param Concluded $concluded
     */
    protected function applyConcluded(Concluded $concluded)
    {
        $this->concluded = true;
    }
}
