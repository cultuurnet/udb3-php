<?php

namespace CultuurNet\UDB3;

use ValueObjects\DateTime\WeekDay;

class OpeningHours
{
    /**
     * @var OpeningHour[]
     */
    private $openingHours;

    /**
     * OpeningHours constructor.
     * @param OpeningHour[]|null $openingHours
     */
    public function __construct(array $openingHours = null)
    {
        $this->openingHours = $openingHours ? $openingHours : null;
    }

    /**
     * @param OpeningHour $openingHour
     */
    public function addOpeningHour(OpeningHour $openingHour)
    {
        $this->openingHours[] = $openingHour;
    }

    /**
     * @return OpeningHour[]
     */
    public function getOpeningHours()
    {
        return $this->openingHours;
    }

    /**
     * @return Weekday[]
     */
    public function getWeekDays()
    {
        $weekdays = [];
        
        foreach ($this->openingHours as $openingHour) {
            $weekdays[] = $openingHour->getWeekDay();
        }
        
        return $weekdays;
    }

    /**
     * @param OpeningHour $otherOpeningHour
     * @return bool
     */
    public function equalOpeningHour(OpeningHour $otherOpeningHour)
    {
        $equal = false;

        foreach ($this->openingHours as $openingHour) {
            if ($openingHour->equalHours($otherOpeningHour)) {
                $equal = true;
                break;
            }
        }

        return $equal;
    }
}
