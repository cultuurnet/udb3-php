<?php

namespace CultuurNet\UDB3;

use DateTime;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class TimestampTest extends TestCase
{
    const START_DATE_KEY = 'startDate';
    const END_DATE_KEY = 'endDate';

    const START_DATE = '2016-01-03T01:01:01+01:00';
    const END_DATE = '2016-01-07T01:01:01+01:00';

    /**
     * @var Timestamp
     */
    private $timestamp;

    public function setUp()
    {
        $this->timestamp = new Timestamp(
            DateTime::createFromFormat(DateTime::ATOM, self::START_DATE),
            DateTime::createFromFormat(DateTime::ATOM, self::END_DATE)
        );
    }

    /**
     * @test
     */
    public function it_stores_a_start_and_end_date()
    {
        $this->assertEquals(
            DateTime::createFromFormat(DateTime::ATOM, self::START_DATE),
            $this->timestamp->getStartDate()
        );

        $this->assertEquals(
            DateTime::createFromFormat(DateTime::ATOM, self::END_DATE),
            $this->timestamp->getEndDate()
        );
    }

    /**
     * @test
     */
    public function it_has_the_exact_original_state_after_serialization_and_deserialization()
    {
        $serialized = $this->timestamp->serialize();
        $jsonEncoded = json_encode($serialized);

        $jsonDecoded = json_decode($jsonEncoded, true);
        $deserialized = Timestamp::deserialize($jsonDecoded);

        $this->assertEquals($this->timestamp, $deserialized);
    }

    /**
     * @test
     */
    public function its_end_date_can_not_be_earlier_than_start_date()
    {
        $pastDate = '2016-01-03T00:01:01+01:00';

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('End date can not be earlier than start date.');

        new Timestamp(
            DateTime::createFromFormat(DateTime::ATOM, self::START_DATE),
            DateTime::createFromFormat(DateTime::ATOM, $pastDate)
        );
    }

    /**
     * @test
     * @dataProvider timestampProvider
     */
    public function it_can_check_for_equality(Timestamp $otherTimestamp, bool $equal): void
    {
        $timestamp = new Timestamp(
            new DateTime('2016-01-03T01:01:01+01:00'),
            new DateTime('2016-01-07T01:01:01+01:00')
        );

        $this->assertEquals(
            $equal,
            $timestamp->equals($otherTimestamp)
        );
    }

    public function timestampProvider(): array
    {
        return [
            'equal timestamp' => [
                new Timestamp(
                    new DateTime('2016-01-03T01:01:01+01:00'),
                    new DateTime('2016-01-07T01:01:01+01:00')
                ),
                true,
            ],
            'timestamp with different start date'=> [
                new Timestamp(
                    new DateTime('2016-01-05T01:01:01+01:00'),
                    new DateTime('2016-01-07T01:01:01+01:00')
                ),
                false,
            ],
            'timestamp with different end date' => [
                new Timestamp(
                    new DateTime('2016-01-03T01:01:01+01:00'),
                    new DateTime('2016-01-08T01:01:01+01:00')
                ),
                false,
            ],
            'timestamp with different start and end date' => [
                new Timestamp(
                    new DateTime('2016-01-05T01:01:01+01:00'),
                    new DateTime('2016-01-09T01:01:01+01:00')
                ),
                false,
            ],
        ];
    }
}
