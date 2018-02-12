<?php

namespace CultuurNet\UDB3\Place\Events;

use CultuurNet\UDB3\Address\Address;
use CultuurNet\UDB3\Calendar;
use CultuurNet\UDB3\Event\EventType;
use CultuurNet\UDB3\Offer\Commands\AbstractCreateCommand;
use CultuurNet\UDB3\Theme;
use CultuurNet\UDB3\Title;
use DateTimeImmutable;

class CreatePlace extends AbstractCreateCommand
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
    private $theme = null;

    /**
     * @var Address
     */
    private $address;

    /**
     * @var Calendar
     */
    private $calendar;

    /**
     * @var DateTimeImmutable|null
     */
    private $publicationDate = null;

    /**
     * @param string $eventId
     * @param Title $title
     * @param Address $address
     * @param EventType $eventType
     * @param Calendar $calendar
     * @param Theme|null $theme
     * @param DateTimeImmutable|null $publicationDate
     */
    public function __construct(
        $eventId,
        Title $title,
        EventType $eventType,
        Address $address,
        Calendar $calendar,
        Theme $theme = null,
        DateTimeImmutable $publicationDate = null
    ) {
        parent::__construct($eventId);

        $this->title = $title;
        $this->eventType = $eventType;
        $this->address = $address;
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
}
