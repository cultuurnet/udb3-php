<?php

namespace CultuurNet\UDB3\Event\Events;

use CultuurNet\UDB3\Calendar;
use CultuurNet\UDB3\CalendarInterface;
use CultuurNet\UDB3\Offer\Events\AbstractEvent;

class EventCopied extends AbstractEvent
{
    /**
     * @var string
     */
    private $originalEventUuid;

    /**
     * @var CalendarInterface|Calendar
     */
    private $calendar;

    /**
     * EventCopied constructor.
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

    /**
     * @inheritdoc
     */
    public function serialize()
    {
        return parent::serialize() + [
                'original_event_id' => $this->getOriginalEventUuid(),
                'calendar' => $this->calendar->serialize()
            ];
    }

    /**
     * @inheritdoc
     */
    public static function deserialize(array $data)
    {
        return new static(
            $data['item_id'],
            $data['original_event_id'],
            Calendar::deserialize($data['calendar'])
        );
    }
}
