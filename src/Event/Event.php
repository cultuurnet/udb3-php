<?php

namespace CultuurNet\UDB3\Event;

use Broadway\EventSourcing\EventSourcedAggregateRoot;
use CultuurNet\UDB3\BookingInfo;
use CultuurNet\UDB3\CalendarInterface;
use CultuurNet\UDB3\Cdb\EventItemFactory;
use CultuurNet\UDB3\ContactPoint;
use CultuurNet\UDB3\Event\Events\BookingInfoUpdated;
use CultuurNet\UDB3\Event\Events\ContactPointUpdated;
use CultuurNet\UDB3\Event\Events\DescriptionUpdated;
use CultuurNet\UDB3\Event\Events\EventCreated;
use CultuurNet\UDB3\Event\Events\EventImportedFromUDB2;
use CultuurNet\UDB3\Event\Events\EventUpdatedFromUDB2;
use CultuurNet\UDB3\Event\Events\EventWasLabelled;
use CultuurNet\UDB3\Event\Events\ImageAdded;
use CultuurNet\UDB3\Event\Events\ImageDeleted;
use CultuurNet\UDB3\Event\Events\ImageUpdated;
use CultuurNet\UDB3\Event\Events\MajorInfoUpdated;
use CultuurNet\UDB3\Event\Events\OrganizerDeleted;
use CultuurNet\UDB3\Event\Events\OrganizerUpdated;
use CultuurNet\UDB3\Event\Events\TypicalAgeRangeUpdated;
use CultuurNet\UDB3\Event\Events\Unlabelled;
use CultuurNet\UDB3\Label;
use CultuurNet\UDB3\Language;
use CultuurNet\UDB3\Location;
use CultuurNet\UDB3\MediaObject;
use CultuurNet\UDB3\Title;

class Event extends EventSourcedAggregateRoot
{
    protected $eventId;
    protected $labels = array();

    const MAIN_LANGUAGE_CODE = 'nl';

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
     * {@inheritdoc}
     */
    public function getAggregateRootId()
    {
        return $this->eventId;
    }

    public function getLabels()
    {
        return $this->labels;
    }

    public function label(Label $label)
    {
        if (in_array($label, $this->labels)) {
            return;
        }

        $this->apply(new EventWasLabelled($this->eventId, $label));
    }

    public function unlabel(Label $label)
    {
        if (!in_array($label, $this->labels)) {
            return;
        }
        $this->apply(new Unlabelled($this->eventId, $label));
    }

    protected function applyEventCreated(EventCreated $eventCreated)
    {
        $this->eventId = $eventCreated->getEventId();
    }

    protected function applyEventWasLabelled(EventWasLabelled $eventLabelled)
    {
        $this->labels[] = $eventLabelled->getLabel();
    }

    protected function applyUnlabelled(Unlabelled $unlabelled)
    {
        $this->labels = array_filter(
            $this->labels,
            function (Label $label) use ($unlabelled) {
                return $label != $unlabelled->getLabel();
            }
        );
    }

    protected function applyEventImportedFromUDB2(
        EventImportedFromUDB2 $eventImported
    ) {
        $this->eventId = $eventImported->getEventId();

        $udb2Event = EventItemFactory::createEventFromCdbXml(
            $eventImported->getCdbXmlNamespaceUri(),
            $eventImported->getCdbXml()
        );

        $this->labels = array();
        foreach (array_values($udb2Event->getKeywords()) as $udb2Keyword) {
            $keyword = trim($udb2Keyword);
            if ($keyword) {
                $this->labels[] = new Label($keyword);
            }
        }
    }

    /**
     * @param Language $language
     * @param string $title
     */
    public function translateTitle(Language $language, $title)
    {
        $this->apply(new TitleTranslated($this->eventId, $language, $title));
    }

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
     * @param MediaObject $mediaObject
     */
    public function addImage(MediaObject $mediaObject)
    {
        $this->apply(new ImageAdded($this->eventId, $mediaObject));
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

    protected function applyTitleTranslated(TitleTranslated $titleTranslated)
    {
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
}
