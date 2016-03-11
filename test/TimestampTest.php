<?php

namespace CultuurNet\UDB3;

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

    public function setUp()
    {
        $this->timestamp = new Timestamp(self::START_DATE, self::END_DATE);
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
    public function it_has_the_exact_original_state_after_serialization_and_deserialization()
    {
        $serialized = $this->timestamp->serialize();
        $deserialized = Timestamp::deserialize($serialized);
        $this->assertEquals($this->timestamp, $deserialized);
    }
}
