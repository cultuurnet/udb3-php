<?php

namespace CultuurNet\UDB3;

use UnexpectedValueException;

class CalendarTest extends \PHPUnit_Framework_TestCase
{
    const START_DATE = '2016-03-06T10:00:00';
    const END_DATE = '2016-03-13T12:00:00';

    const TIMESTAMP_1 = '1457254800';
    const TIMESTAMP_1_START_DATE = '2016-03-06T10:00:00';
    const TIMESTAMP_1_END_DATE = '2016-03-06T10:00:00';
    const TIMESTAMP_2 = '1457859600';
    const TIMESTAMP_2_START_DATE = '2016-03-13T10:00:00';
    const TIMESTAMP_2_END_DATE = '2016-03-13T12:00:00';

    /**
     * @var Calendar
     */
    private $calendar;

    /**
     * @var array
     */
    private $calendarAsArray;

    public function setUp()
    {
        $timestamp1 = new Timestamp(
            self::TIMESTAMP_1_START_DATE,
            self::TIMESTAMP_1_END_DATE
        );

        $timestamp2 = new Timestamp(
            self::TIMESTAMP_2_START_DATE,
            self::TIMESTAMP_2_END_DATE
        );

        $this->calendar = new Calendar(
            Calendar::MULTIPLE,
            self::START_DATE,
            self::END_DATE,
            array(
                self::TIMESTAMP_1 => $timestamp1,
                self::TIMESTAMP_2 => $timestamp2
            )
        );

        $this->calendarAsArray = array(
            'type' => Calendar::MULTIPLE,
            'startDate' => self::START_DATE,
            'endDate' => self::END_DATE,
            'timestamps' => array(
                self::TIMESTAMP_1 => array(
                    'startDate' => self::TIMESTAMP_1_START_DATE,
                    'endDate' => self::TIMESTAMP_1_END_DATE,
                ),
                self::TIMESTAMP_2 => array(
                    'startDate' => self::TIMESTAMP_2_START_DATE,
                    'endDate' => self::TIMESTAMP_2_END_DATE,
                )
            ),
            'openingHours' => array()
        );
    }

    /**
     * @test
     * @expectedException UnexpectedValueException
     */
    public function it_validates_calendar_type()
    {
        new Calendar('unknown');
    }

    /**
     * @test
     * @expectedException UnexpectedValueException
     */
    public function it_validates_start_date()
    {
        new Calendar('multiple');
    }

    /**
     * @test
     */
    public function it_serializes()
    {
        $actual = $this->calendar->serialize();

        $this->assertEquals($this->calendarAsArray, $actual);
    }

    /**
     * @test
     */
    public function it_deserializes()
    {
        $calendar = Calendar::deserialize($this->calendarAsArray);

        $this->assertEquals($this->calendar, $calendar);
    }
}
