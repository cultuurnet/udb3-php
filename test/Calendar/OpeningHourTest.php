<?php

namespace CultuurNet\UDB3\Calendar;

use ValueObjects\DateTime\Hour;
use ValueObjects\DateTime\Minute;

class OpeningHourTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var OpeningTime
     */
    private $opens;

    /**
     * @var OpeningTime
     */
    private $closes;

    /**
     * @var DayOfWeek[]
     */
    private $weekDays;

    /**
     * @var array
     */
    private $openingHourAsArray;

    /**
     * @var OpeningHour
     */
    private $openingHour;

    protected function setUp()
    {
        $this->opens = new OpeningTime(new Hour(9), new Minute(30));

        $this->closes = new OpeningTime(new Hour(17), new Minute(0));

        $this->weekDays = [
            DayOfWeek::fromNative('monday'),
            DayOfWeek::fromNative('tuesday'),
            DayOfWeek::fromNative('wednesday'),
            DayOfWeek::fromNative('thursday'),
            DayOfWeek::fromNative('friday'),
        ];

        $this->openingHourAsArray = [
            'opens' => '09:30',
            'closes' => '17:00',
            'dayOfWeek' => [
                'monday',
                'tuesday',
                'wednesday',
                'thursday',
                'friday',
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
    public function it_stores_an_opens_time()
    {
        $this->assertEquals(
            $this->opens,
            $this->openingHour->getOpens()
        );
    }

    /**
     * @test
     */
    public function it_stores_a_closes_time()
    {
        $this->assertEquals(
            $this->closes,
            $this->openingHour->getCloses()
        );
    }

    /**
     * @test
     */
    public function it_can_compare_on_hours()
    {
        $sameOpeningHour = new OpeningHour(
            new OpeningTime(new Hour(9), new Minute(30)),
            new OpeningTime(new Hour(17), new Minute(0)),
            DayOfWeek::MONDAY()
        );

        $differentOpeningHour = new OpeningHour(
            new OpeningTime(new Hour(10), new Minute(30)),
            new OpeningTime(new Hour(17), new Minute(0)),
            DayOfWeek::MONDAY()
        );

        $this->assertTrue(
            $this->openingHour->hasEqualHours($sameOpeningHour)
        );
        $this->assertFalse(
            $this->openingHour->hasEqualHours($differentOpeningHour)
        );
    }

    /**
     * @test
     */
    public function it_stores_weekdays()
    {
        $this->assertEquals(
            $this->weekDays,
            $this->openingHour->getDaysOfWeek()
        );
    }

    /**
     * @test
     */
    public function it_can_deserialize()
    {
        $this->assertEquals(
            $this->openingHour,
            OpeningHour::deserialize($this->openingHourAsArray)
        );
    }

    /**
     * @test
     */
    public function it_can_serialize()
    {
        $this->assertEquals(
            $this->openingHourAsArray,
            $this->openingHour->serialize()
        );
    }
}
