<?php

namespace CultuurNet\UDB3\Calendar;

use CultuurNet\UDB3\Model\ValueObject\Calendar\OpeningHours\Day;
use ValueObjects\Enum\Enum;

/**
 * Created custom value object instead of using WeekDay to avoid changing
 * casing of the first letter.
 *
 * @method static DayOfWeek MONDAY()
 * @method static DayOfWeek TUESDAY()
 * @method static DayOfWeek WEDNESDAY()
 * @method static DayOfWeek THURSDAY()
 * @method static DayOfWeek FRIDAY()
 * @method static DayOfWeek SATURDAY()
 * @method static DayOfWeek SUNDAY()
 *
 * @todo Replace by CultuurNet\UDB3\Model\ValueObject\Calendar\OpeningHours\Day.
 */
class DayOfWeek extends Enum
{
    const MONDAY = 'monday';
    const TUESDAY = 'tuesday';
    const WEDNESDAY = 'wednesday';
    const THURSDAY = 'thursday';
    const FRIDAY = 'friday';
    const SATURDAY = 'saturday';
    const SUNDAY = 'sunday';

    /**
     * @param Day $day
     * @return DayOfWeek
     */
    public static function fromUdb3ModelDay(Day $day)
    {
        return self::fromNative($day->toString());
    }
}
