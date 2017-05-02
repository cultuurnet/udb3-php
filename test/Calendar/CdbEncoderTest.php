<?php

namespace CultuurNet\UDB3\Calendar;

use CultureFeed_Cdb_Data_Calendar_Period;
use CultureFeed_Cdb_Data_Calendar_PeriodList;
use CultureFeed_Cdb_Data_Calendar_Permanent;
use CultuurNet\UDB3\Calendar;

use CultuurNet\UDB3\CalendarType;
use CultuurNet\UDB3\Timestamp;
use DateTime;
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

    /**
     * @test
     */
    public function it_encodes_a_calendar_with_single_timestamp_as_a_cdb_calendar_object()
    {
        $encoder = new CdbEncoder();
        $expectedCalendar = new \CultureFeed_Cdb_Data_Calendar_TimestampList();
        $expectedCalendar->add(new \CultureFeed_Cdb_Data_Calendar_Timestamp(
            '2017-01-24',
            '08:00:00',
            '18:00:00'
        ));

        $calendar = new Calendar(
            CalendarType::SINGLE(),
            new DateTime('2017-01-24T08:00:00.000000+0000'),
            new DateTime('2017-01-24T18:00:00.000000+0000'),
            [
                new Timestamp(
                    new DateTime('2017-01-24T08:00:00.000000+0000'),
                    new DateTime('2017-01-24T18:00:00.000000+0000')
                )
            ]
        );

        $cdbCalendar = $encoder->encode($calendar, 'cdb');

        $this->assertEquals($expectedCalendar, $cdbCalendar);
    }

    /**
     * @test
     */
    public function it_encodes_a_calendar_with_multiple_timestamps_as_a_cdb_calendar_object()
    {
        $encoder = new CdbEncoder();
        $expectedCalendar = new \CultureFeed_Cdb_Data_Calendar_TimestampList();
        $expectedCalendar->add(new \CultureFeed_Cdb_Data_Calendar_Timestamp(
            '2017-01-24',
            '08:00:00',
            '18:00:00'
        ));
        $expectedCalendar->add(new \CultureFeed_Cdb_Data_Calendar_Timestamp(
            '2017-01-25',
            '08:00:00',
            '18:00:00'
        ));
        $expectedCalendar->add(new \CultureFeed_Cdb_Data_Calendar_Timestamp(
            '2017-01-26',
            '08:00:00',
            '18:00:00'
        ));

        $calendar = new Calendar(
            CalendarType::MULTIPLE(),
            new DateTime('2017-01-24T08:00:00.000000+0000'),
            new DateTime('2017-01-26T18:00:00.000000+0000'),
            [
                new Timestamp(
                    new DateTime('2017-01-24T08:00:00.000000+0000'),
                    new DateTime('2017-01-24T18:00:00.000000+0000')
                ),
                new Timestamp(
                    new DateTime('2017-01-25T08:00:00.000000+0000'),
                    new DateTime('2017-01-25T18:00:00.000000+0000')
                ),
                new Timestamp(
                    new DateTime('2017-01-26T08:00:00.000000+0000'),
                    new DateTime('2017-01-26T18:00:00.000000+0000')
                ),
            ]
        );

        $cdbCalendar = $encoder->encode($calendar, 'cdb');

        $this->assertEquals($expectedCalendar, $cdbCalendar);
    }

    /**
     * @test
     */
    public function it_encodes_permanent_calendar_with_weekscheme_as_a_cdb_calendar_object()
    {
        $encoder = new CdbEncoder();

        $weekDays = new DayOfWeekCollection(
            DayOfWeek::MONDAY(),
            DayOfWeek::TUESDAY(),
            DayOfWeek::WEDNESDAY(),
            DayOfWeek::THURSDAY(),
            DayOfWeek::FRIDAY()
        );

        $weekendDays = new DayOfWeekCollection(
            DayOfWeek::SATURDAY(),
            DayOfWeek::SUNDAY()
        );

        $calendar = new Calendar(
            CalendarType::PERMANENT(),
            null,
            null,
            [],
            [
                new OpeningHour(
                    new OpeningTime(new Hour(9), new Minute(0)),
                    new OpeningTime(new Hour(12), new Minute(0)),
                    $weekDays
                ),
                new OpeningHour(
                    new OpeningTime(new Hour(13), new Minute(0)),
                    new OpeningTime(new Hour(17), new Minute(0)),
                    $weekDays
                ),
                new OpeningHour(
                    new OpeningTime(new Hour(10), new Minute(0)),
                    new OpeningTime(new Hour(16), new Minute(0)),
                    $weekendDays
                ),
            ]
        );

        $expectedCalendar = new CultureFeed_Cdb_Data_Calendar_Permanent();
        $weekScheme = \CultureFeed_Cdb_Data_Calendar_Weekscheme::parseFromCdbXml(
            simplexml_load_file(__DIR__ . '/../week_scheme.xml')
        );
        $expectedCalendar->setWeekScheme($weekScheme);

        $cdbCalendar = $encoder->encode($calendar, 'cdb');

        $this->assertEquals($expectedCalendar, $cdbCalendar);
    }

    /**
     * @test
     */
    public function it_encodes_periodic_calendar_with_weekscheme_as_a_cdb_calendar_object()
    {
        $encoder = new CdbEncoder();

        $weekDays = new DayOfWeekCollection(
            DayOfWeek::MONDAY(),
            DayOfWeek::TUESDAY(),
            DayOfWeek::WEDNESDAY(),
            DayOfWeek::THURSDAY(),
            DayOfWeek::FRIDAY()
        );

        $weekendDays = new DayOfWeekCollection(
            DayOfWeek::SATURDAY(),
            DayOfWeek::SUNDAY()
        );

        $calendar = new Calendar(
            CalendarType::PERIODIC(),
            new DateTime('2017-01-24T00:00:00.000000+0000'),
            new DateTime('2018-01-24T00:00:00.000000+0000'),
            [],
            [
                new OpeningHour(
                    new OpeningTime(new Hour(9), new Minute(0)),
                    new OpeningTime(new Hour(12), new Minute(0)),
                    $weekDays
                ),
                new OpeningHour(
                    new OpeningTime(new Hour(13), new Minute(0)),
                    new OpeningTime(new Hour(17), new Minute(0)),
                    $weekDays
                ),
                new OpeningHour(
                    new OpeningTime(new Hour(10), new Minute(0)),
                    new OpeningTime(new Hour(16), new Minute(0)),
                    $weekendDays
                ),
            ]
        );

        $weekScheme = \CultureFeed_Cdb_Data_Calendar_Weekscheme::parseFromCdbXml(
            simplexml_load_file(__DIR__ . '/../week_scheme.xml')
        );

        $expectedPeriod = new CultureFeed_Cdb_Data_Calendar_Period('2017-01-24', '2018-01-24');
        $expectedPeriod->setWeekScheme($weekScheme);
        $expectedCalendar = new CultureFeed_Cdb_Data_Calendar_PeriodList();
        $expectedCalendar->add($expectedPeriod);

        $cdbCalendar = $encoder->encode($calendar, 'cdb');

        $this->assertEquals($expectedCalendar, $cdbCalendar);
    }
}
