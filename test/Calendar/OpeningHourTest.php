<?php

namespace CultuurNet\UDB3\Calendar;

use ValueObjects\DateTime\Hour;
use ValueObjects\DateTime\Minute;
use ValueObjects\DateTime\Second;
use ValueObjects\DateTime\Time;
use ValueObjects\DateTime\WeekDay;

class OpeningHourTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Time
     */
    private $opens;

    /**
     * @var Time
     */
    private $closes;

    /**
     * @var WeekDay[]
     */
    private $weekDays;

    /**
     * @var array
     */
    private $openingHoursAsArray;

    /**
     * @var OpeningHour
     */
    private $openingHour;

    protected function setUp()
    {
        $this->opens = new Time(new Hour(9), new Minute(30), new Second(0));

        $this->closes = new Time(new Hour(17), new Minute(0), new Second(0));

        $this->weekDays = [
            WeekDay::fromNative(WeekDay::MONDAY),
            WeekDay::fromNative(WeekDay::TUESDAY),
            WeekDay::fromNative(WeekDay::WEDNESDAY),
            WeekDay::fromNative(WeekDay::THURSDAY),
            WeekDay::fromNative(WeekDay::FRIDAY),
        ];

        $this->openingHoursAsArray = [
            'opens' => '09:30:00',
            'closes' => '17:00:00',
            'dayOfWeek' => [
                'Monday',
                'Tuesday',
                'Wednesday',
                'Thursday',
                'Friday',
            ],
        ];

        $this->openingHour = new OpeningHour(
            $this->opens,
            $this->closes,
            ...$this->weekDays
        );
    }

    /**
     * @test
     */
    public function it_stores_an_opening_time()
    {
        $this->assertEquals(
            $this->opens,
            $this->openingHour->getOpens()
        );
    }

    /**
     * @test
     */
    public function it_stores_an_closing_time()
    {
        $this->assertEquals(
            $this->closes,
            $this->openingHour->getCloses()
        );
    }

    /**
     * @test
     */
    public function it_stores_weekdays()
    {
        $this->assertEquals(
            $this->weekDays,
            $this->openingHour->getWeekDays()
        );
    }

    /**
     * @test
     */
    public function it_can_deserialize()
    {
        $this->assertEquals(
            $this->openingHour,
            OpeningHour::deserialize($this->openingHoursAsArray)
        );
    }

    /**
     * @test
     */
    public function it_can_serialize()
    {
        $this->assertEquals(
            $this->openingHoursAsArray,
            $this->openingHour->serialize()
        );
    }
}
