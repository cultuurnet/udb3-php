<?php

namespace CultuurNet\UDB3;

use ValueObjects\DateTime\Hour;
use ValueObjects\DateTime\Minute;
use ValueObjects\DateTime\Second;
use ValueObjects\DateTime\Time;
use ValueObjects\DateTime\WeekDay;

class OpeningHourTest extends \PHPUnit_Framework_TestCase
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
     * @var OpeningHour
     */
    private $openingHourMonday;

    /**
     * @var OpeningHour
     */
    private $openingHourTuesday;

    /**
     * @var OpeningHour
     */
    private $openingHourSunday;

    protected function setUp()
    {
        $this->weekDay = WeekDay::MONDAY();
        $this->opens = new Time(new Hour(8), new Minute(30), new Second(0));
        $this->closes = new Time(new Hour(16), new Minute(30), new Second(0));

        $this->openingHourMonday = new OpeningHour(
            $this->weekDay,
            $this->opens,
            $this->closes
        );

        $this->openingHourTuesday = new OpeningHour(
            WeekDay::TUESDAY(),
            new Time(new Hour(8), new Minute(30), new Second(0)),
            new Time(new Hour(16), new Minute(30), new Second(0))
        );

        $this->openingHourSunday = new OpeningHour(
            WeekDay::SUNDAY(),
            new Time(new Hour(9), new Minute(0), new Second(0)),
            new Time(new Hour(12), new Minute(30), new Second(0))
        );
    }

    /**
     * @test
     */
    public function it_stores_a_week_day()
    {
        $this->assertEquals(
            $this->weekDay,
            $this->openingHourMonday->getWeekDay()
        );
    }

    /**
     * @test
     */
    public function it_stores_open_time()
    {
        $this->assertEquals(
            $this->opens,
            $this->openingHourMonday->getOpens()
        );
    }

    /**
     * @test
     */
    public function it_stores_close_time()
    {
        $this->assertEquals(
            $this->closes,
            $this->openingHourMonday->getCloses()
        );
    }

    /**
     * @test
     */
    public function it_can_tell_when_opening_hours_have_the_same_open_and_close_time()
    {
        $this->assertTrue(
            $this->openingHourMonday->equalHours(
                $this->openingHourTuesday
            )
        );

        $this->assertFalse(
            $this->openingHourMonday->equalHours(
                $this->openingHourSunday
            )
        );
    }
}
