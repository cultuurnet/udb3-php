<?php

namespace CultuurNet\UDB3;

use DateTime;
use DateTimeInterface;
use UnexpectedValueException;

class CalendarTest extends \PHPUnit_Framework_TestCase
{
    const START_DATE = '2016-03-06T10:00:00+01:00';
    const END_DATE = '2016-03-13T12:00:00+01:00';

    const TIMESTAMP_1 = '1457254800';
    const TIMESTAMP_1_START_DATE = '2016-03-06T10:00:00+01:00';
    const TIMESTAMP_1_END_DATE = '2016-03-06T10:00:00+01:00';
    const TIMESTAMP_2 = '1457859600';
    const TIMESTAMP_2_START_DATE = '2016-03-13T10:00:00+01:00';
    const TIMESTAMP_2_END_DATE = '2016-03-13T12:00:00+01:00';

    /**
     * @var Calendar
     */
    private $calendar;

    public function setUp()
    {
        $timestamp1 = new Timestamp(
            DateTime::createFromFormat(DateTime::ATOM, self::TIMESTAMP_1_START_DATE),
            DateTime::createFromFormat(DateTime::ATOM, self::TIMESTAMP_1_END_DATE)
        );

        $timestamp2 = new Timestamp(
            DateTime::createFromFormat(DateTime::ATOM, self::TIMESTAMP_2_START_DATE),
            DateTime::createFromFormat(DateTime::ATOM, self::TIMESTAMP_2_END_DATE)
        );

        $this->calendar = new Calendar(
            CalendarType::MULTIPLE(),
            DateTime::createFromFormat(DateTime::ATOM, self::START_DATE),
            DateTime::createFromFormat(DateTime::ATOM, self::END_DATE),
            array(
                self::TIMESTAMP_1 => $timestamp1,
                self::TIMESTAMP_2 => $timestamp2
            )
        );
    }

    /**
     * @test
     * @dataProvider calendarTypesWithStartDateProvider
     * @param CalendarType $calendarType
     * @param string $expectedMessage
     * @param DateTimeInterface|null $startDate
     */
    public function it_should_expect_a_start_date_for_some_calendar_types(
        CalendarType $calendarType,
        $expectedMessage,
        DateTimeInterface $startDate = null
    ) {
        $this->setExpectedException(UnexpectedValueException::class, $expectedMessage);
        new Calendar($calendarType, $startDate);
    }

    public function calendarTypesWithStartDateProvider()
    {
        return [
            'for MULTIPLE calendar type' => [
                'calendarType' => CalendarType::MULTIPLE(),
                'expectedMessage' => 'Start date can not be empty for calendar type: multiple.',
                'startDate' => null,
            ],
            'for SINGLE calendar type' => [
                'calendarType' => CalendarType::SINGLE(),
                'expectedMessage' => 'Start date can not be empty for calendar type: single.',
                'startDate' => null,
            ],
        ];
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

    /**
     * @test
     * @dataProvider jsonldCalendarProvider
     */
    public function it_should_generate_the_expected_json_for_a_calendar_of_each_type(
        Calendar $calendar,
        $jsonld
    ) {
        $this->assertEquals($jsonld, $calendar->toJsonLd());
    }

    public function jsonldCalendarProvider()
    {
        return [
            'single' => [
                'calendar' => new Calendar(
                    CalendarType::SINGLE(),
                    DateTime::createFromFormat(DateTime::ATOM, self::START_DATE),
                    DateTime::createFromFormat(DateTime::ATOM, self::END_DATE)
                ),
                'jsonld' => [
                    'calendarType' => 'single',
                    'startDate' => '2016-03-06T10:00:00+01:00',
                    'endDate' => '2016-03-13T12:00:00+01:00',
                ]
            ],
            'multiple' => [
                'calendar' => new Calendar(
                    CalendarType::MULTIPLE(),
                    DateTime::createFromFormat(DateTime::ATOM, self::START_DATE),
                    DateTime::createFromFormat(DateTime::ATOM, self::END_DATE)
                ),
                'jsonld' => [
                    'calendarType' => 'multiple',
                    'startDate' => '2016-03-06T10:00:00+01:00',
                    'endDate' => '2016-03-13T12:00:00+01:00',
                ]
            ],
            'periodic' => [
                'calendar' => new Calendar(
                    CalendarType::PERIODIC(),
                    DateTime::createFromFormat(DateTime::ATOM, self::START_DATE),
                    DateTime::createFromFormat(DateTime::ATOM, self::END_DATE)
                ),
                'jsonld' => [
                    'calendarType' => 'periodic',
                    'startDate' => '2016-03-06T10:00:00+01:00',
                    'endDate' => '2016-03-13T12:00:00+01:00',
                ]
            ],
            'permanent' => [
                'calendar' => new Calendar(
                    CalendarType::PERMANENT()
                ),
                'jsonld' => [
                    'calendarType' => 'permanent',
                ]
            ],
        ];
    }

    /**
     * @test
     */
    public function it_should_assume_the_timezone_is_Brussels_when_none_is_provided_when_deserializing()
    {
        $oldCalendarData = [
            'type' => 'single',
            'startDate' => '2016-03-06T10:00:00',
            'endDate' => '2016-03-13T12:00:00',
            'timestamps' => []
        ];

        $expectedCalendar = new Calendar(
            CalendarType::SINGLE(),
            DateTime::createFromFormat(DateTime::ATOM, self::START_DATE),
            DateTime::createFromFormat(DateTime::ATOM, self::END_DATE)
        );

        $calendar = Calendar::deserialize($oldCalendarData);


        $this->assertEquals($expectedCalendar, $calendar);
    }
}
