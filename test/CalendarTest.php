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
    public function it_has_the_exact_original_state_after_serialization_and_deserialization()
    {
        $serialized = $this->calendar->serialize();
        $jsonEncoded = json_encode($serialized);

        $jsonDecoded = json_decode($jsonEncoded, true);
        $deserialized = Calendar::deserialize($jsonDecoded);

        $this->assertEquals($this->calendar, $deserialized);
    }
}
