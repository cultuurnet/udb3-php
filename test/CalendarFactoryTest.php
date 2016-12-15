<?php

namespace CultuurNet\UDB3;

use CultureFeed_Cdb_Data_Calendar_Timestamp;
use CultureFeed_Cdb_Data_Calendar_TimestampList;
use DateTimeImmutable;
use DateTimeZone;
use PHPUnit_Framework_TestCase;

class CalendarFactoryTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var CalendarFactory
     */
    private $factory;

    public function setUp()
    {
        $this->factory = new CalendarFactory();
    }

    /**
     * @test
     */
    public function it_drops_timestamp_timeend_before_timestart()
    {
        $cdbCalendar = new CultureFeed_Cdb_Data_Calendar_TimestampList();
        $cdbCalendar->add(
            new CultureFeed_Cdb_Data_Calendar_Timestamp(
                "2016-12-16",
                "21:00:00",
                "05:00:00"
            )
        );

        $calendar = $this->factory->createFromCdbCalendar($cdbCalendar);

        $expectedTimeZone = new DateTimeZone('Europe/Brussels');
        $expectedStartDate = $expectedEndDate = new DateTimeImmutable(
            '2016-12-16 21:00:00',
            $expectedTimeZone
        );

        $expectedCalendar = new Calendar(
            CalendarType::SINGLE(),
            $expectedStartDate,
            $expectedEndDate,
            [
                new Timestamp($expectedStartDate, $expectedEndDate),
            ]
        );

        $this->assertEquals($expectedCalendar, $calendar);
    }
}
