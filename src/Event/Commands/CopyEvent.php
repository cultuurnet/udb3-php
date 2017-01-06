<?php

namespace CultuurNet\UDB3\Event\Commands;

use CultuurNet\UDB3\CalendarInterface;
use CultuurNet\UDB3\Offer\Commands\AbstractCommand;

class CopyEvent extends AbstractCommand
{
    /**
     * @var string
     */
    private $originalEventId;

    /**
     * @var CalendarInterface
     */
    private $calendar;

    /**
     * CopyEvent constructor.
     * @param string $eventId
     * @param string $originalEventId
     * @param CalendarInterface $calendar
     */
    public function __construct(
        $eventId,
        $originalEventId,
        CalendarInterface $calendar
    ) {
        parent::__construct($eventId);

        $this->originalEventId = $originalEventId;
        $this->calendar = $calendar;
    }

    /**
     * @return string
     */
    public function getOriginalEventId()
    {
        return $this->originalEventId;
    }

    /**
     * @return CalendarInterface
     */
    public function getCalendar()
    {
        return $this->calendar;
    }
}
