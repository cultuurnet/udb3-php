<?php

namespace CultuurNet\UDB3\Place\Events;

use CultuurNet\UDB3\Address\Address;
use CultuurNet\UDB3\Calendar;
use CultuurNet\UDB3\CalendarInterface;
use CultuurNet\UDB3\Event\EventType;
use CultuurNet\UDB3\Language;
use CultuurNet\UDB3\Place\PlaceEvent;
use CultuurNet\UDB3\Theme;
use CultuurNet\UDB3\Title;
use DateTimeImmutable;

/**
 * Event when a place is created.
 */
class PlaceCreated extends PlaceEvent
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
    private $eventType;

    /**
     * @var Theme
     */
    private $theme = null;

    /**
     * @var Address
     */
    private $address;

    /**
     * @var CalendarInterface
     */
    private $calendar;

    /**
     * @var DateTimeImmutable|null
     */
    private $publicationDate = null;

    /**
     * @param string $placeId
     * @param Language $mainLanguage
     * @param Title $title
     * @param EventType $eventType
     * @param Address $address
     * @param CalendarInterface $calendar
     * @param Theme|null $theme
     * @param DateTimeImmutable|null $publicationDate
     */
    public function __construct(
        $placeId,
        Language $mainLanguage,
        Title $title,
        EventType $eventType,
        Address $address,
        CalendarInterface $calendar,
        Theme $theme = null,
        DateTimeImmutable $publicationDate = null
    ) {
        parent::__construct($placeId);

        $this->mainLanguage = $mainLanguage;
        $this->title = $title;
        $this->eventType = $eventType;
        $this->address = $address;
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
     * @return Address
     */
    public function getAddress()
    {
        return $this->address;
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
            'title' => (string) $this->getTitle(),
            'event_type' => $this->getEventType()->serialize(),
            'theme' => $theme,
            'address' => $this->getAddress()->serialize(),
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
        return new static(
            $data['place_id'],
            new Language($data['main_language']),
            new Title($data['title']),
            EventType::deserialize($data['event_type']),
            Address::deserialize($data['address']),
            Calendar::deserialize($data['calendar']),
            $theme,
            $publicationDate
        );
    }
}
