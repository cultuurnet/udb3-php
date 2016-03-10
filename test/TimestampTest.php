<?php

namespace CultuurNet\UDB3;

use ValueObjects\DateTime\Time;

class TimestampTest extends \PHPUnit_Framework_TestCase
{
    const START_DATE_KEY = 'startDate';
    const END_DATE_KEY = 'endDate';

    const START_DATE = '2016-01-03T01:01:01';
    const END_DATE = '2016-01-07T01:01:01';

    /**
     * @var Timestamp
     */
    private $timestamp;

    /**
     * @var array
     */
    private $timestampAsArray;

    public function setUp()
    {
        $this->timestamp = new Timestamp(self::START_DATE, self::END_DATE);

        $this->timestampAsArray = array(
            self::START_DATE_KEY => self::START_DATE,
            self::END_DATE_KEY => self::END_DATE
        );
    }

    /**
     * @test
     */
    public function it_stores_a_start_and_end_date()
    {
        $this->assertEquals(self::START_DATE, $this->timestamp->getStartDate());
        $this->assertEquals(self::END_DATE, $this->timestamp->getEndDate());
    }

    /**
     * @test
     */
    public function it_deserializes()
    {
        $timestamp = Timestamp::deserialize($this->timestampAsArray);

        $this->assertEquals(self::START_DATE, $timestamp->getStartDate());
        $this->assertEquals(self::END_DATE, $timestamp->getEndDate());
    }

    /**
     * @test
     */
    public function it_serializes()
    {
        $actual = $this->timestamp->serialize();

        $this->assertEquals($this->timestampAsArray, $actual);
    }
}
