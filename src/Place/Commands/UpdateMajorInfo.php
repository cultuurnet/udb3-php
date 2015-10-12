<?php

/**
 * @file
 * Contains CultuurNet\UDB3\Place\Commands\UpdateMajorInfo.
 */

namespace CultuurNet\UDB3\Place\Commands;

use CultuurNet\UDB3\Address;
use CultuurNet\UDB3\CalendarInterface;
use CultuurNet\UDB3\Event\EventType;
use CultuurNet\UDB3\Theme;
use CultuurNet\UDB3\Title;

/**
 * Provides a command to update the major info of the place.
 */
class UpdateMajorInfo
{

    /**
     * @var string
     */
    private $id;

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
     * @var Address
     */
    private $address;

    /**
     * @var CalendarInterface
     */
    private $calendar;

    /**
     * @param string $eventId
     * @param Title $title
     * @param string $location
     * @param CalendarInterface $calendar
     */
    public function __construct($placeId, Title $title, EventType $eventType, Address $address, CalendarInterface $calendar, $theme = null)
    {
        $this->id = $placeId;
        $this->title = $title;
        $this->eventType = $eventType;
        $this->address = $address;
        $this->calendar = $calendar;
        $this->theme = $theme;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
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
     * @return Theme | null
     */
    public function getTheme()
    {
        return $this->theme;
    }

    /**
     * @return Address
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @return CalendarInterface
     */
    public function getCalendar()
    {
        return $this->calendar;
    }
}
