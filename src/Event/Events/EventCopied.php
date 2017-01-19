<?php

namespace CultuurNet\UDB3\Event\Events;

use CultuurNet\UDB3\Calendar;
use CultuurNet\UDB3\CalendarInterface;
use CultuurNet\UDB3\EventSourcing\AggregateCopiedEventInterface;
use CultuurNet\UDB3\Offer\Events\AbstractEvent;

class EventCopied extends AbstractEvent implements AggregateCopiedEventInterface
{
    /**
     * @var string
     */
    private $originalEventId;

    /**
     * @var CalendarInterface|Calendar
     */
    private $calendar;

    /**
     * EventCopied constructor.
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

        if (!is_string($originalEventId)) {
            throw new \InvalidArgumentException(
                'Expected originalEventId to be a string, received ' . gettype($originalEventId)
            );
        }

        $this->originalEventId = $originalEventId;
        $this->calendar = $calendar;
    }

    /**
     * @inheritdoc
     */
    public function getParentAggregateId()
    {
        return $this->originalEventId;
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

    /**
     * @inheritdoc
     */
    public function serialize()
    {
        return parent::serialize() + [
                'original_event_id' => $this->getOriginalEventId(),
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
