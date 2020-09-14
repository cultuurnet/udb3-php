<?php

namespace CultuurNet\UDB3\Event\Events;

use CultuurNet\UDB3\Calendar;
use CultuurNet\UDB3\CalendarInterface;
use CultuurNet\UDB3\Event\EventEvent;
use CultuurNet\UDB3\Event\EventType;
use CultuurNet\UDB3\Language;
use CultuurNet\UDB3\Event\ValueObjects\LocationId;
use CultuurNet\UDB3\Theme;
use CultuurNet\UDB3\Title;
use DateTimeImmutable;

/**
 * Event when an event is created.
 */
final class EventCreated extends EventEvent
{
    /**
     * @var Language
     */
    private $mainLanguage;

    /**
     * @var Title
     */
    private $title;

    /**
     * @var EventType
     */
    private $eventType = null;

    /**
     * @var Theme
     */
    private $theme = null;

    /**
     * @var LocationId
     */
    private $location;

    /**
     * @var CalendarInterface
     */
    private $calendar;

    /**
     * @var DateTimeImmutable|null
     */
    private $publicationDate = null;

    /**
     * @param string $eventId
     * @param Language $mainLanguage
     * @param Title $title
     * @param EventType $eventType
     * @param LocationId $location
     * @param CalendarInterface $calendar
     * @param Theme|null $theme
     * @param DateTimeImmutable|null $publicationDate
     */
    public function __construct(
        $eventId,
        Language $mainLanguage,
        Title $title,
        EventType $eventType,
        LocationId $location,
        CalendarInterface $calendar,
        Theme $theme = null,
        DateTimeImmutable $publicationDate = null
    ) {
        parent::__construct($eventId);

        $this->mainLanguage = $mainLanguage;
        $this->title = $title;
        $this->eventType = $eventType;
        $this->location = $location;
        $this->calendar = $calendar;
        $this->theme = $theme;
        $this->publicationDate = $publicationDate;
    }

    /**
     * @return Language
     */
    public function getMainLanguage()
    {
        return $this->mainLanguage;
    }

    /**
     * @return Title
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return EventType
     */
    public function getEventType()
    {
        return $this->eventType;
    }

    /**
     * @return Theme
     */
    public function getTheme()
    {
        return $this->theme;
    }

    /**
     * @return CalendarInterface
     */
    public function getCalendar()
    {
        return $this->calendar;
    }

    /**
     * @return LocationId
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * @return DateTimeImmutable|null
     */
    public function getPublicationDate()
    {
        return $this->publicationDate;
    }

    /**
     * @return array
     */
    public function serialize()
    {
        $theme = null;
        if ($this->getTheme() !== null) {
            $theme = $this->getTheme()->serialize();
        }
        $publicationDate = null;
        if (!is_null($this->getPublicationDate())) {
            $publicationDate = $this->getPublicationDate()->format(\DateTime::ATOM);
        }
        return parent::serialize() + array(
            'main_language' => $this->mainLanguage->getCode(),
            'title' => (string)$this->getTitle(),
            'event_type' => $this->getEventType()->serialize(),
            'theme' => $theme,
            'location' => $this->getLocation()->toNative(),
            'calendar' => $this->getCalendar()->serialize(),
            'publication_date' => $publicationDate,
        );
    }

    /**
     * @return static
     */
    public static function deserialize(array $data)
    {
        $theme = null;
        if (!empty($data['theme'])) {
            $theme = Theme::deserialize($data['theme']);
        }
        $publicationDate = null;
        if (!empty($data['publication_date'])) {
            $publicationDate = DateTimeImmutable::createFromFormat(
                \DateTime::ATOM,
                $data['publication_date']
            );
        }
        return new self(
            $data['event_id'],
            new Language($data['main_language']),
            new Title($data['title']),
            EventType::deserialize($data['event_type']),
            new LocationId($data['location']),
            Calendar::deserialize($data['calendar']),
            $theme,
            $publicationDate
        );
    }
}
