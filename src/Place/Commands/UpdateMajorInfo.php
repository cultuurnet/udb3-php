<?php

namespace CultuurNet\UDB3\Place\Commands;

use CultuurNet\UDB3\Address\Address;
use CultuurNet\UDB3\CalendarInterface;
use CultuurNet\UDB3\Event\EventType;
use CultuurNet\UDB3\Offer\Commands\AbstractCommand;
use CultuurNet\UDB3\Theme;
use CultuurNet\UDB3\Title;

class UpdateMajorInfo extends AbstractCommand
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
     * @var Address
     */
    private $address;

    /**
     * @var CalendarInterface
     */
    private $calendar;

    /**
     * @param $placeId
     * @param Title $title
     * @param EventType $eventType
     * @param Address $address
     * @param CalendarInterface $calendar
     * @param null $theme
     */
    public function __construct(
        $placeId,
        Title $title,
        EventType $eventType,
        Address $address,
        CalendarInterface $calendar,
        $theme = null
    ) {
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
     * @return Theme | null
     */
    public function getTheme()
    {
        return $this->theme;
    }

    /**
     * @return \CultuurNet\UDB3\Address\Address
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
