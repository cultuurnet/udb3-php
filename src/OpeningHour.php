<?php

namespace CultuurNet\UDB3;

use ValueObjects\DateTime\Time;
use ValueObjects\DateTime\WeekDay;

class OpeningHour
{
    /**
     * @var WeekDay
     */
    private $weekDay;

    /**
     * @var Time
     */
    private $opens;

    /**
     * @var Time
     */
    private $closes;

    /**
     * OpeningHour constructor.
     * @param WeekDay $weekDay
     * @param Time $opens
     * @param Time $closes
     */
    public function __construct(
        WeekDay $weekDay,
        Time $opens,
        Time $closes
    ) {
        $this->weekDay = $weekDay;
        $this->opens = $opens;
        $this->closes = $closes;
    }

    /**
     * @param OpeningHour $otherOpeningHour
     * @return bool
     */
    public function equalHours(OpeningHour $otherOpeningHour)
    {
        return $otherOpeningHour->getOpens()->sameValueAs($this->getOpens()) &&
            $otherOpeningHour->getCloses()->sameValueAs($this->getCloses());
    }

    /**
     * @return WeekDay
     */
    public function getWeekDay()
    {
        return $this->weekDay;
    }

    /**
     * @return Time
     */
    public function getOpens()
    {
        return $this->opens;
    }

    /**
     * @return Time
     */
    public function getCloses()
    {
        return $this->closes;
    }
}
