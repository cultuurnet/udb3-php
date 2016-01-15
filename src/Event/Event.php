<?php

namespace CultuurNet\UDB3\Event;

use Broadway\EventSourcing\EventSourcedAggregateRoot;
use CultuurNet\UDB3\BookingInfo;
use CultuurNet\UDB3\CalendarInterface;
use CultuurNet\UDB3\Cdb\EventItemFactory;
use CultuurNet\UDB3\CollaborationDataCollection;
use CultuurNet\UDB3\ContactPoint;
use CultuurNet\UDB3\Event\Events\BookingInfoUpdated;
use CultuurNet\UDB3\Event\Events\ContactPointUpdated;
use CultuurNet\UDB3\Event\Events\DescriptionUpdated;
use CultuurNet\UDB3\Event\Events\EventCdbXMLInterface;
use CultuurNet\UDB3\Event\Events\EventCreated;
use CultuurNet\UDB3\Event\Events\EventCreatedFromCdbXml;
use CultuurNet\UDB3\Event\Events\EventDeleted;
use CultuurNet\UDB3\Event\Events\EventImportedFromUDB2;
use CultuurNet\UDB3\Event\Events\EventUpdatedFromCdbXml;
use CultuurNet\UDB3\Event\Events\EventUpdatedFromUDB2;
use CultuurNet\UDB3\Event\Events\EventWasLabelled;
use CultuurNet\UDB3\Event\Events\ImageAdded;
use CultuurNet\UDB3\Event\Events\ImageDeleted;
use CultuurNet\UDB3\Event\Events\ImageUpdated;
use CultuurNet\UDB3\Event\Events\LabelsMerged;
use CultuurNet\UDB3\Event\Events\CollaborationDataAdded;
use CultuurNet\UDB3\Event\Events\MajorInfoUpdated;
use CultuurNet\UDB3\Event\Events\OrganizerDeleted;
use CultuurNet\UDB3\Event\Events\OrganizerUpdated;
use CultuurNet\UDB3\Event\Events\TranslationApplied;
use CultuurNet\UDB3\Event\Events\TranslationDeleted;
use CultuurNet\UDB3\Event\Events\TypicalAgeRangeDeleted;
use CultuurNet\UDB3\Event\Events\TypicalAgeRangeUpdated;
use CultuurNet\UDB3\Event\Events\Unlabelled;
use CultuurNet\UDB3\EventXmlString;
use CultuurNet\UDB3\Label;
use CultuurNet\UDB3\LabelCollection;
use CultuurNet\UDB3\Language;
use CultuurNet\UDB3\CollaborationData;
use CultuurNet\UDB3\Location;
use CultuurNet\UDB3\Media\Image;
use CultuurNet\UDB3\Media\MediaObject;
use CultuurNet\UDB3\Media\Properties\MIMEType;
use CultuurNet\UDB3\Title;
use CultuurNet\UDB3\Translation;
use ValueObjects\Identity\UUID;
use ValueObjects\String\String;
use ValueObjects\Web\Url;

class Event extends EventSourcedAggregateRoot
{
    protected $eventId;

    /**
     * @var LabelCollection
     */
    protected $labels;

    /**
     * @var Translation[]
     */
    protected $translations = [];

    /**
     * @var CollaborationDataCollection[]
     *   Array of different collections, keyed by language.
     */
    protected $collaborationData;

    /**
     * @var UUID[]
     */
    protected $mediaObjects = [];

    const MAIN_LANGUAGE_CODE = 'nl';

    public function __construct()
    {
        $this->resetLabels();
    }

    /**
     * Factory method to create a new event.
     *
     * @param Title $title
     * @param EventType $eventType
     * @param Location $location
     * @param CalendarBase $calendar
     * @param Theme/null $theme
     *
     * @return Event
     */
    public static function create($eventId, Title $title, EventType $eventType, Location $location, CalendarInterface $calendar, $theme = null)
    {
        if (!is_string($eventId)) {
            throw new \InvalidArgumentException(
                'Expected eventId to be a string, received ' . gettype($eventId)
            );
        }
        $event = new self();
        $event->apply(new EventCreated($eventId, $title, $eventType, $location, $calendar, $theme));

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
     * @param String $eventId
     * @param String $cdbXmlNamespaceUri
     * @return Event
     */
    public static function createFromCdbXml(
        String $eventId,
        EventXmlString $xmlString,
        String $cdbXmlNamespaceUri
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
     * @param String $eventId
     * @param EventXmlString $xmlString
     * @param String $cdbXmlNamespaceUri
     * @return Event
     */
    public function updateFromCdbXml(
        String $eventId,
        EventXmlString $xmlString,
        String $cdbXmlNamespaceUri
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
                new String($this->eventId),
                $labels
            )
        );
    }

    /**
     * @param Language $language
     * @param String|null $title
     * @param String|null $shortDescription
     * @param String|null $longDescription
     */
    public function applyTranslation(
        Language $language,
        String $title = null,
        String $shortDescription = null,
        String $longDescription = null
    ) {
        $this->apply(
            new TranslationApplied(
                new String($this->eventId),
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
                new String($this->eventId),
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
            new String($this->eventId),
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
     * @return LabelCollection
     */
    public function getLabels()
    {
        return $this->labels;
    }

    /**
     * @return UUID[]
     */
    public function getMediaObjects()
    {
        return $this->mediaObjects;
    }

    /**
     * @param Label $label
     */
    public function label(Label $label)
    {
        if (!$this->labels->contains($label)) {
            $this->apply(new EventWasLabelled($this->eventId, $label));
        }
    }

    /**
     * @param Label $label
     */
    public function unlabel(Label $label)
    {
        if ($this->labels->contains($label)) {
            $this->apply(new Unlabelled($this->eventId, $label));
        }
    }

    protected function applyEventCreated(EventCreated $eventCreated)
    {
        $this->eventId = $eventCreated->getEventId();
    }

    protected function applyEventWasLabelled(EventWasLabelled $eventLabelled)
    {
        $newLabel = $eventLabelled->getLabel();

        if (!$this->labels->contains($newLabel)) {
            $this->labels = $this->labels->with($newLabel);
        }
    }

    protected function applyUnlabelled(Unlabelled $unlabelled)
    {
        $removedLabel = $unlabelled->getLabel();

        $this->labels = $this->labels->without($removedLabel);
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
     * @param Language $language
     * @param string $title
     */
    public function translateTitle(Language $language, $title)
    {
        $this->apply(new TitleTranslated($this->eventId, $language, $title));
    }

    /**
     * @param Language $language
     * @param string $description
     */
    public function translateDescription(Language $language, $description)
    {
        $this->apply(
            new DescriptionTranslated($this->eventId, $language, $description)
        );
    }

    /**
     * @param string $description
     */
    public function updateDescription($description)
    {
        $this->apply(new DescriptionUpdated($this->eventId, $description));
    }

    /**
     * @param string $typicalAgeRange
     */
    public function updateTypicalAgeRange($typicalAgeRange)
    {
        $this->apply(new TypicalAgeRangeUpdated($this->eventId, $typicalAgeRange));
    }

    public function deleteTypicalAgeRange()
    {
        $this->apply(new TypicalAgeRangeDeleted($this->eventId));
    }

    /**
     * @param string $organizerId
     */
    public function updateOrganizer($organizerId)
    {
        $this->apply(new OrganizerUpdated($this->eventId, $organizerId));
    }

    /**
     * Delete the given organizer.
     *
     * @param string $organizerId
     */
    public function deleteOrganizer($organizerId)
    {
        $this->apply(new OrganizerDeleted($this->eventId, $organizerId));
    }

    /**
     * Updated the contact info.
     *
     * @param array $phones
     * @param array $emails
     * @param array $urls
     */
    public function updateContactPoint(ContactPoint $contactPoint)
    {
        $this->apply(new ContactPointUpdated($this->eventId, $contactPoint));
    }

    /**
     * Updated the booking info.
     *
     * @param BookingInfo $bookingInfo
     */
    public function updateBookingInfo(BookingInfo $bookingInfo)
    {
        $this->apply(new BookingInfoUpdated($this->eventId, $bookingInfo));
    }

    /**
     * Add a new image.
     *
     * @param Image $image
     * @throws DuplicateMediaObjectException
     */
    public function addImage(Image $image)
    {
        $duplicateMediaObject = array_filter(
            $this->getMediaObjects(),
            function ($existingMediaObjectId) use ($image) {
                return $image
                    ->getMediaObjectId()
                    ->sameValueAs($existingMediaObjectId);
            }
        );

        if (empty($duplicateMediaObject)) {
            $this->apply(new ImageAdded($this->eventId, $image));
        }
    }

    /**
     * Update an image.
     *
     * @param int $indexToUpdate
     * @param MediaObject $mediaObject
     */
    public function updateImage($indexToUpdate, MediaObject $mediaObject)
    {
        $this->apply(new ImageUpdated($this->eventId, $indexToUpdate, $mediaObject));
    }

    /**
     * Delete an image.
     *
     * @param int $indexToDelete
     * @param mixed int|string $internalId
     */
    public function deleteImage($indexToDelete, $internalId)
    {
        $this->apply(new ImageDeleted($this->eventId, $indexToDelete, $internalId));
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
    public function updateMajorInfo(Title $title, EventType $eventType, Location $location, CalendarInterface $calendar, $theme = null)
    {
        $this->apply(new MajorInfoUpdated($this->eventId, $title, $eventType, $location, $calendar, $theme));
    }

    /**
     * Delete this item.
     */
    public function deleteEvent()
    {
        $this->apply(new EventDeleted($this->eventId));
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

    protected function resetLabels()
    {
        $this->labels = new LabelCollection();
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

    protected function applyImageAdded(ImageAdded $imageAdded)
    {
        $this->mediaObjects[] = $imageAdded->getImage()->getMediaObjectId();
    }
}
