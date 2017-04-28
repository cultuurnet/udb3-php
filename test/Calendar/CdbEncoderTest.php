<?php

namespace CultuurNet\UDB3\Calendar;

use CultuurNet\UDB3\Calendar;

use CultuurNet\UDB3\CalendarType;
use ValueObjects\DateTime\Hour;
use ValueObjects\DateTime\Minute;

class CdbEncoderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_encodes_a_permanent_calendar_as_a_cdb_calendar_object()
    {
        $encoder = new CdbEncoder();
        $calendar = new Calendar(CalendarType::PERMANENT());

        $cdbCalendar = $encoder->encode($calendar, 'cdb');
        $expectedCalendar = new \CultureFeed_Cdb_Data_Calendar_Permanent();

        $this->assertEquals($expectedCalendar, $cdbCalendar);
    }
}
