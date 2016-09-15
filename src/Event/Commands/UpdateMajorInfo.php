<?php

/**
 * @file
 * Contains CultuurNet\UDB3\Event\Commands\UpdateMajorInfo.
 */

namespace CultuurNet\UDB3\Event\Commands;

use CultuurNet\UDB3\CalendarInterface;
use CultuurNet\UDB3\Event\EventType;
use CultuurNet\UDB3\Location\Location;
use CultuurNet\UDB3\Theme;
use CultuurNet\UDB3\Title;

/**
 * Provides a command to update the major info of the event.
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
     * @var Location
     */
    private $location;

    /**
     * @var CalendarInterface
     */
    private $calendar;

    /**
     * UpdateMajorInfo constructor.
     * @param $eventId
     * @param Title $title
     * @param EventType $eventType
     * @param \CultuurNet\UDB3\Location\Location $location
     * @param CalendarInterface $calendar
     * @param Theme|null $theme
     */
    public function __construct(
        $eventId,
        Title $title,
        EventType $eventType,
        Location $location,
        CalendarInterface $calendar,
        Theme $theme = null
    ) {
        $this->id = $eventId;
        $this->title = $title;
        $this->eventType = $eventType;
        $this->location = $location;
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
     * @return Theme|null
     */
    public function getTheme()
    {
        return $this->theme;
    }

    /**
     * @return Location
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * @return CalendarInterface
     */
    public function getCalendar()
    {
        return $this->calendar;
    }
}
