<?php

namespace CultuurNet\UDB3;

use ValueObjects\DateTime\Hour;
use ValueObjects\DateTime\Minute;
use ValueObjects\DateTime\Second;
use ValueObjects\DateTime\Time;
use ValueObjects\DateTime\WeekDay;

class OpeningHoursTest extends \PHPUnit_Framework_TestCase
{
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

    /**
     * @var OpeningHours
     */
    private $openingHours;

    protected function setUp()
    {
        $this->openingHourMonday = new OpeningHour(
            WeekDay::MONDAY(),
            new Time(new Hour(8), new Minute(30), new Second(0)),
            new Time(new Hour(16), new Minute(30), new Second(0))
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

        $this->openingHours = new OpeningHours(
            [
                $this->openingHourMonday,
                $this->openingHourSunday,
            ]
        );
    }

    /**
     * @test
     */
    public function it_stores_opening_hours()
    {
        $this->assertEquals(
            [
                $this->openingHourMonday,
                $this->openingHourSunday,
            ],
            $this->openingHours->getOpeningHours()
        );
    }

    /**
     * @test
     */
    public function it_can_add_an_opening_hour()
    {
        $this->openingHours->addOpeningHour($this->openingHourTuesday);

        $this->assertEquals(
            [
                $this->openingHourMonday,
                $this->openingHourSunday,
                $this->openingHourTuesday
            ],
            $this->openingHours->getOpeningHours()
        );
    }

    /**
     * @test
     */
    public function it_can_get_week_days()
    {
        $this->assertEquals(
            [
                WeekDay::MONDAY(),
                WeekDay::SUNDAY(),
            ],
            $this->openingHours->getWeekDays()
        );
    }

    /**
     * @test
     */
    public function it_can_determine_same_opening_hour()
    {
        $this->assertTrue(
            $this->openingHours->equalOpeningHour($this->openingHourTuesday)
        );
    }
}
