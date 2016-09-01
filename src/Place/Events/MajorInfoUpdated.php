<?php

/**
 * @file
 * Contains CultuurNet\UDB3\Place\Events\MajorInfoUpdated.
 */
namespace CultuurNet\UDB3\Place\Events;

use CultuurNet\UDB3\Address\Address;
use CultuurNet\UDB3\Calendar;
use CultuurNet\UDB3\CalendarInterface;
use CultuurNet\UDB3\Event\EventType;
use CultuurNet\UDB3\Location;
use CultuurNet\UDB3\Place\PlaceEvent;
use CultuurNet\UDB3\Theme;
use CultuurNet\UDB3\Title;

/**
 * Provides a majorInfoUpdated event.
 */
class MajorInfoUpdated extends PlaceEvent
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
     * @var \CultuurNet\UDB3\Address\Address
     */
    private $address;

    /**
     * @var CalendarInterface
     */
    private $calendar;

    /**
     * @param string $placeId
     * @param Title $title
     * @param string $location
     * @param CalendarInterface $calendar
     */
    public function __construct($placeId, Title $title, EventType $eventType, Address $address, CalendarInterface $calendar, $theme = null)
    {
        parent::__construct($placeId);

        $this->title = $title;
        $this->eventType = $eventType;
        $this->address = $address;
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
     * @return Address
     */
    public function getAddress()
    {
        return $this->address;
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
            'address' => $this->getAddress()->serialize(),
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
            $data['place_id'],
            new Title($data['title']),
            EventType::deserialize($data['event_type']),
            Address::deserialize($data['address']),
            Calendar::deserialize($data['calendar']),
            $theme
        );
    }
}
