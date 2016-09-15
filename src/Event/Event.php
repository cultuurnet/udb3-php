<?php

namespace CultuurNet\UDB3\Event;

use CultuurNet\UDB3\BookingInfo;
use CultuurNet\UDB3\CalendarInterface;
use CultuurNet\UDB3\Cdb\EventItemFactory;
use CultuurNet\UDB3\Cdb\UpdateableWithCdbXmlInterface;
use CultuurNet\UDB3\CollaborationData;
use CultuurNet\UDB3\CollaborationDataCollection;
use CultuurNet\UDB3\ContactPoint;
use CultuurNet\UDB3\Event\Events\BookingInfoUpdated;
use CultuurNet\UDB3\Event\Events\CollaborationDataAdded;
use CultuurNet\UDB3\Event\Events\ContactPointUpdated;
use CultuurNet\UDB3\Event\Events\DescriptionTranslated;
use CultuurNet\UDB3\Event\Events\DescriptionUpdated;
use CultuurNet\UDB3\Event\Events\EventCdbXMLInterface;
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
use CultuurNet\UDB3\Event\Events\OrganizerDeleted;
use CultuurNet\UDB3\Event\Events\OrganizerUpdated;
use CultuurNet\UDB3\Event\Events\TitleTranslated;
use CultuurNet\UDB3\Event\Events\TranslationApplied;
use CultuurNet\UDB3\Event\Events\TranslationDeleted;
use CultuurNet\UDB3\Event\Events\TypicalAgeRangeDeleted;
use CultuurNet\UDB3\Event\Events\TypicalAgeRangeUpdated;
use CultuurNet\UDB3\EventXmlString;
use CultuurNet\UDB3\Label;
use CultuurNet\UDB3\LabelCollection;
use CultuurNet\UDB3\Language;
use CultuurNet\UDB3\Location\Location;
use CultuurNet\UDB3\Offer\Commands\Image\AbstractUpdateImage;
use CultuurNet\UDB3\Offer\Offer;
use CultuurNet\UDB3\Media\Image;
use CultuurNet\UDB3\Theme;
use CultuurNet\UDB3\Title;
use CultuurNet\UDB3\Translation;
use ValueObjects\Identity\UUID;
use ValueObjects\String\String as StringLiteral;

class Event extends Offer implements UpdateableWithCdbXmlInterface
{
    protected $eventId;

    /**
     * @var Translation[]
     */
    protected $translations = [];

    /**
     * @var CollaborationDataCollection[]
     *   Array of different collections, keyed by language.
     */
    protected $collaborationData;

    const MAIN_LANGUAGE_CODE = 'nl';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Factory method to create a new event.
     *
     * @param Title $title
     * @param EventType $eventType
     * @param Location $location
     * @param CalendarInterface $calendar
     * @param Theme|null $theme
     * @param \DateTimeImmutable|null $publicationDate
     *
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
        if (!is_string($eventId)) {
            throw new \InvalidArgumentException(
                'Expected eventId to be a string, received ' . gettype($eventId)
            );
        }

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
     * @param EventXmlString $xmlString
     * @param StringLiteral $eventId
     * @param StringLiteral $cdbXmlNamespaceUri
     * @return Event
     */
    public static function createFromCdbXml(
        StringLiteral $eventId,
        EventXmlString $xmlString,
        StringLiteral $cdbXmlNamespaceUri
    ) {
        $event = new self();
        $event->apply(
            new EventCreatedFromCdbXml(
                $eventId,
                $xmlString,
                $cdbXmlNamespaceUri
            )
        );

        return $event;
    }

    /**
     * @param StringLiteral $eventId
     * @param EventXmlString $xmlString
     * @param StringLiteral $cdbXmlNamespaceUri
     * @return Event
     */
    public function updateFromCdbXml(
        StringLiteral $eventId,
        EventXmlString $xmlString,
        StringLiteral $cdbXmlNamespaceUri
    ) {
        $this->apply(
            new EventUpdatedFromCdbXml(
                $eventId,
                $xmlString,
                $cdbXmlNamespaceUri
            )
        );
    }

    /**
     * @param LabelCollection $labels
     */
    public function mergeLabels(LabelCollection $labels)
    {
        if (count($labels) === 0) {
            throw new \InvalidArgumentException(
                'Argument $labels should contain at least one label'
            );
        }

        $this->apply(
            new LabelsMerged(
                new StringLiteral($this->eventId),
                $labels
            )
        );
    }

    /**
     * @param Language $language
     * @param StringLiteral|null $title
     * @param StringLiteral|null $shortDescription
     * @param StringLiteral|null $longDescription
     */
    public function applyTranslation(
        Language $language,
        StringLiteral $title = null,
        StringLiteral $shortDescription = null,
        StringLiteral $longDescription = null
    ) {
        $this->apply(
            new TranslationApplied(
                new StringLiteral($this->eventId),
                $language,
                $title,
                $shortDescription,
                $longDescription
            )
        );
    }

    /**
     * @param Language $language
     */
    public function deleteTranslation(
        Language $language
    ) {
        if (!array_key_exists($language->getCode(), $this->translations)) {
            return;
        }

        $this->apply(
            new TranslationDeleted(
                new StringLiteral($this->eventId),
                $language
            )
        );
    }

    /**
     * @param Language $language
     * @param CollaborationData $collaborationData
     * @return bool
     */
    protected function isSameCollaborationDataAlreadyPresent(
        Language $language,
        CollaborationData $collaborationData
    ) {
        if (!isset($this->collaborationData[$language->getCode()])) {
            return false;
        }

        $languageCollaborationData = $this->collaborationData[$language->getCode()];

        return $languageCollaborationData->contains($collaborationData);
    }

    /**
     * @param Language $language
     * @param \CultuurNet\UDB3\CollaborationData $collaborationData
     */
    public function addCollaborationData(
        Language $language,
        CollaborationData $collaborationData
    ) {
        if ($this->isSameCollaborationDataAlreadyPresent($language, $collaborationData)) {
            return;
        }

        $collaborationDataAdded = new CollaborationDataAdded(
            new StringLiteral($this->eventId),
            $language,
            $collaborationData
        );

        $this->apply($collaborationDataAdded);
    }

    /**
     * {@inheritdoc}
     */
    public function getAggregateRootId()
    {
        return $this->eventId;
    }

    /**
     * @return Translation[]
     */
    public function getTranslations()
    {
        return $this->translations;
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

        $this->setLabelsFromUDB2Event($udb2Event);
    }

    /**
     * Update the major info.
     *
     * @param Title $title
     * @param EventType $eventType
     * @param Location $location
     * @param CalendarInterface $calendar
     * @param type $theme
     */
    public function updateMajorInfo(
        Title $title,
        EventType $eventType,
        Location $location,
        CalendarInterface $calendar,
        $theme = null
    ) {
        $this->apply(new MajorInfoUpdated($this->eventId, $title, $eventType, $location, $calendar, $theme));
    }

    /**
     * @param \CultureFeed_Cdb_Item_Event $udb2Event
     */
    protected function setLabelsFromUDB2Event(\CultureFeed_Cdb_Item_Event $udb2Event)
    {
        $this->resetLabels();

        /** @var \CultureFeed_Cdb_Data_Keyword $udb2Keyword */
        foreach (array_values($udb2Event->getKeywords(true)) as $udb2Keyword) {
            $keyword = trim($udb2Keyword->getValue());
            if ($keyword) {
                $this->labels = $this->labels->with(
                    new Label($keyword, $udb2Keyword->isVisible())
                );
            }
        }
    }

    protected function applyEventCreatedFromCdbXml(
        EventCreatedFromCdbXml $eventCreatedFromCdbXml
    ) {
        $this->eventId = $eventCreatedFromCdbXml->getEventId()->toNative();

        $udb2Event = EventItemFactory::createEventFromCdbXml(
            $eventCreatedFromCdbXml->getCdbXmlNamespaceUri(),
            $eventCreatedFromCdbXml->getEventXmlString()->toEventXmlString()
        );

        $this->setLabelsFromUDB2Event($udb2Event);
    }

    protected function applyEventUpdatedFromCdbXml(
        EventUpdatedFromCdbXml $eventUpdatedFromCdbXml
    ) {
        $this->eventId = $eventUpdatedFromCdbXml->getEventId()->toNative();

        $udb2Event = EventItemFactory::createEventFromCdbXml(
            $eventUpdatedFromCdbXml->getCdbXmlNamespaceUri(),
            $eventUpdatedFromCdbXml->getEventXmlString()->toEventXmlString()
        );

        $this->setLabelsFromUDB2Event($udb2Event);
    }

    protected function applyLabelsMerged(
        LabelsMerged $labelsMerged
    ) {
        $this->labels = $this->labels->merge($labelsMerged->getLabels());
    }

    protected function applyTranslationApplied(
        TranslationApplied $translationApplied
    ) {
        $this->eventId = $translationApplied->getEventId()->toNative();

        $language = $translationApplied->getLanguage()->getCode();
        $translation = new Translation(
            $translationApplied->getLanguage(),
            $translationApplied->getTitle(),
            $translationApplied->getShortdescription(),
            $translationApplied->getLongdescription()
        );

        if (!array_key_exists($language, $this->translations)) {
            $this->translations[$language] = $translation;
        } else {
            $newTranslation = $this->translations[$language]->mergeTranslation($translation);
            $this->translations[$language] = $newTranslation;
        }
    }

    protected function applyTranslationDeleted(
        TranslationDeleted $translationDeleted
    ) {
        $language = $translationDeleted->getLanguage()->getCode();

        if (array_key_exists($language, $this->translations)) {
            unset($this->translations[$language]);
        }
    }

    protected function applyCollaborationDataAdded(
        CollaborationDataAdded $collaborationDataAdded
    ) {
        $language = $collaborationDataAdded->getLanguage()->getCode();
        $collaborationData = $collaborationDataAdded->getCollaborationData();

        if (!isset($this->collaborationData[$language])) {
            $this->collaborationData[$language] = new CollaborationDataCollection();
        }

        if ($this->collaborationData[$language]->contains($collaborationData)) {
            return;
        }

        $this->collaborationData[$language] = $this->collaborationData[$language]
            ->withKey(
                $collaborationData->getSubBrand()->toNative(),
                $collaborationData
            );
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
     * @return LabelDeleted
     */
    protected function createLabelDeletedEvent(Label $label)
    {
        return new LabelDeleted($this->eventId, $label);
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
     * @return EventDeleted
     */
    protected function createOfferDeletedEvent()
    {
        return new EventDeleted($this->eventId);
    }
}
