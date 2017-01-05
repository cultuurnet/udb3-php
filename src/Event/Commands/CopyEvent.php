<?php

namespace CultuurNet\UDB3\Event\Commands;

use CultuurNet\UDB3\CalendarInterface;
use CultuurNet\UDB3\Offer\Commands\AbstractCommand;

class CopyEvent extends AbstractCommand
{
    /**
     * @var string
     */
    private $originalEventUuid;

    /**
     * @var CalendarInterface
     */
    private $calendar;

    /**
     * CopyEvent constructor.
     * @param string $eventId
     * @param string $originalEventUuid
     * @param CalendarInterface $calendar
     */
    public function __construct(
        $eventId,
        $originalEventUuid,
        CalendarInterface $calendar
    ) {
        parent::__construct($eventId);

        $this->originalEventUuid = $originalEventUuid;
        $this->calendar = $calendar;
    }

    /**
     * @return string
     */
    public function getOriginalEventUuid()
    {
        return $this->originalEventUuid;
    }

    /**
     * @return CalendarInterface
     */
    public function getCalendar()
    {
        return $this->calendar;
    }
}
