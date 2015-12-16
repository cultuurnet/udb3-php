<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Event\Events;

use CultuurNet\UDB3\Calendar;
use CultuurNet\UDB3\CalendarInterface;
use CultuurNet\UDB3\Event\EventEvent;
use CultuurNet\UDB3\Event\EventType;
use CultuurNet\UDB3\Location;
use CultuurNet\UDB3\Theme;
use CultuurNet\UDB3\Title;
use DateTime;

/**
 * Event when an event is created.
 */
class EventCreated extends EventEvent
{

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
     * @var Location
     */
    private $location;

    /**
     * @var CalendarInterface
     */
    private $calendar;

    /**
     * @param string $eventId
     * @param Title $title
     * @param string $location
     * @param DateTime $date
     */
    public function __construct($eventId, Title $title, EventType $eventType, Location $location, CalendarInterface $calendar, $theme = null)
    {
        parent::__construct($eventId);

        $this->title = $title;
        $this->eventType = $eventType;
        $this->location = $location;
        $this->calendar = $calendar;
        $this->theme = $theme;
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
     * @return Location
     */
    public function getLocation()
    {
        return $this->location;
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
        return parent::serialize() + array(
            'title' => (string)$this->getTitle(),
            'event_type' => $this->getEventType()->serialize(),
            'theme' => $theme,
            'location' => $this->getLocation()->serialize(),
            'calendar' => $this->getCalendar()->serialize(),
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
        return new static(
            $data['event_id'],
            new Title($data['title']),
            EventType::deserialize($data['event_type']),
            Location::deserialize($data['location']),
            Calendar::deserialize($data['calendar']),
            $theme
        );
    }
}
