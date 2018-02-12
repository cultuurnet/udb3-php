<?php

namespace CultuurNet\UDB3\Event\Commands;

use CultuurNet\UDB3\Calendar;
use CultuurNet\UDB3\Event\EventType;
use CultuurNet\UDB3\Location\Location;
use CultuurNet\UDB3\Offer\Commands\AbstractCreateCommand;
use CultuurNet\UDB3\Theme;
use CultuurNet\UDB3\Title;
use DateTimeImmutable;

class CreateEvent extends AbstractCreateCommand
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
     * @var Calendar
     */
    private $calendar;

    /**
     * @var DateTimeImmutable|null
     */
    private $publicationDate;

    /**
     * @param string $eventId
     * @param Title $title
     * @param EventType $eventType
     * @param Location $location
     * @param Calendar $calendar
     * @param Theme|null $theme
     * @param DateTimeImmutable|null $publicationDate
     */
    public function __construct(
        $eventId,
        Title $title,
        EventType $eventType,
        Location $location,
        Calendar $calendar,
        Theme $theme = null,
        DateTimeImmutable $publicationDate = null
    ) {
        parent::__construct($eventId);

        $this->title = $title;
        $this->eventType = $eventType;
        $this->location = $location;
        $this->calendar = $calendar;
        $this->theme = $theme;
        $this->publicationDate = $publicationDate;
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
     * @return Calendar
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
     * @return DateTimeImmutable|null
     */
    public function getPublicationDate()
    {
        return $this->publicationDate;
    }
}
