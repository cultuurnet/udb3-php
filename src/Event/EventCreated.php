<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Event;

use CultuurNet\UDB3\Location;
use CultuurNet\UDB3\Theme;
use CultuurNet\UDB3\CalendarInterface;

class EventCreated extends EventEvent
{
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
    private $theme;

    /**
     * @var Location
     */
    private $location;

    /**
     * @var CalendarBase
     */
    private $calendar;

    /**
     * @param string $eventId
     * @param Title $title
     * @param string $location
     * @param \DateTime $date
     */
    public function __construct($eventId, Title $title, EventType $eventType, Theme $theme, Location $location, CalendarInterface $calendar)
    {
        parent::__construct($eventId);

        $this->setTitle($title);
        $this->setEventType($eventType);
        $this->setTheme($theme);
        $this->setLocation($location);
        $this->setCalendar($calendar);
    }

    /**
     * @param Title $title
     */
    private function setTitle(Title $title)
    {
        $this->title = $title;
    }

    /**
     * @param EventType $eventType
     */
    private function setEventType(EventType $eventType)
    {
        $this->eventType = $eventType;
    }

    /**
     * @param Theme $theme
     */
    private function setTheme(Theme $theme) {
        $this->theme = $theme;
    }

    /**
     * @param CalendarBase $calendar
     */
    private function setCalendar(CalendarInterface $calendar)
    {
        $this->calendar = $calendar;
    }

    /**
     * @param Location $location
     */
    private function setLocation(Location $location)
    {
        $this->location = $location;
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
     * @return CalendarBase
     */
    public function getCalendar()
    {
        return $this->calendar;
    }

    public function getType()
    {
        return $this->type;
    }

    /**
     * @return Location
     */
    public function getLocation()
    {
        return $this->location;
    }

    private function setType($type)
    {
        $this->type = $type;
    }


    /**
     * @return array
     */
    public function serialize()
    {
        return parent::serialize() + array(
            'title' => (string)$this->getTitle(),
            'event_type' => $this->getEventType()->serialize(),
            'theme' => $this->getTheme()->serialize(),
            'location' => $this->getLocation()->serialize(),
            'calendar' => $this->getCalendar()->serialize(),
            'type' => array(
                'id' => $this->type->getId(),
                'label' => $this->type->getLabel()
            ),
        );
    }

    /**
     * @return static
     */
    public static function deserialize(array $data)
    {
        return new static(
            $data['event_id'],
            new Title($data['title']),
            EventType::deserialize($data['event_type']),
            Theme::deserialize($data['theme']),
            Location::deserialize($data['location']),
            TimeStamps::deserialize($data['calendar']),
            $data['location'],
            \DateTime::createFromFormat('c', $data['date']),
            new EventType(
                $data['type']['id'],
                $data['type']['label']
            )
        );
    }
}
