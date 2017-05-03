<?php

namespace CultuurNet\UDB3\Calendar;

use CultuurNet\UDB3\Calendar;

interface CalendarConverterInterface
{
    /**
     * @param Calendar $calendar
     * @return \CultureFeed_Cdb_Data_Calendar $cdbCalendar
     */
    public function toCdbCalendar(Calendar $calendar);
}
