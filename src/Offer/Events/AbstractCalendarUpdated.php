<?php

namespace CultuurNet\UDB3\Offer\Events;

use CultuurNet\UDB3\Calendar;

abstract class AbstractCalendarUpdated extends AbstractEvent
{
    /**
     * @var Calendar
     */
    private $calendar;

    /**
     * @param string $itemId
     * @param Calendar $calendar
     */
    final public function __construct(string $itemId, Calendar $calendar)
    {
        parent::__construct($itemId);

        $this->calendar = $calendar;
    }

    /**
     * @return Calendar
     */
    public function getCalendar(): Calendar
    {
        return $this->calendar;
    }

    /**
     * @inheritdoc
     */
    public function serialize(): array
    {
        return parent::serialize() + [
                'calendar' => $this->calendar->serialize(),
            ];
    }

    /**
     * @inheritdoc
     */
    public static function deserialize(array $data): AbstractCalendarUpdated
    {
        return new static(
            $data['item_id'],
            Calendar::deserialize($data['calendar'])
        );
    }
}
