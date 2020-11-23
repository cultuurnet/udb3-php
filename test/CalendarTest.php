<?php

namespace CultuurNet\UDB3;

use CultuurNet\UDB3\Calendar\DayOfWeek;
use CultuurNet\UDB3\Calendar\DayOfWeekCollection;
use CultuurNet\UDB3\Calendar\OpeningHour;
use CultuurNet\UDB3\Calendar\OpeningTime;
use CultuurNet\UDB3\Event\ValueObjects\EventStatus;
use CultuurNet\UDB3\Model\ValueObject\Calendar\DateRange;
use CultuurNet\UDB3\Model\ValueObject\Calendar\DateRanges;
use CultuurNet\UDB3\Model\ValueObject\Calendar\MultipleDateRangesCalendar;
use CultuurNet\UDB3\Model\ValueObject\Calendar\OpeningHours\Day;
use CultuurNet\UDB3\Model\ValueObject\Calendar\OpeningHours\Days;
use CultuurNet\UDB3\Model\ValueObject\Calendar\OpeningHours\Hour as Udb3ModelHour;
use CultuurNet\UDB3\Model\ValueObject\Calendar\OpeningHours\Minute as Udb3ModelMinute;
use CultuurNet\UDB3\Model\ValueObject\Calendar\OpeningHours\OpeningHour as Udb3ModelOpeningHour;
use CultuurNet\UDB3\Model\ValueObject\Calendar\OpeningHours\OpeningHours;
use CultuurNet\UDB3\Model\ValueObject\Calendar\OpeningHours\Time;
use CultuurNet\UDB3\Model\ValueObject\Calendar\PeriodicCalendar;
use CultuurNet\UDB3\Model\ValueObject\Calendar\PermanentCalendar;
use CultuurNet\UDB3\Model\ValueObject\Calendar\SingleDateRangeCalendar;
use DateTime;
use PHPUnit\Framework\TestCase;
use ValueObjects\DateTime\Hour;
use ValueObjects\DateTime\Minute;

class CalendarTest extends TestCase
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

        $weekDays = (new DayOfWeekCollection())
            ->addDayOfWeek(DayOfWeek::MONDAY())
            ->addDayOfWeek(DayOfWeek::TUESDAY())
            ->addDayOfWeek(DayOfWeek::WEDNESDAY())
            ->addDayOfWeek(DayOfWeek::THURSDAY())
            ->addDayOfWeek(DayOfWeek::FRIDAY());

        $openingHour1 = new OpeningHour(
            new OpeningTime(new Hour(9), new Minute(0)),
            new OpeningTime(new Hour(12), new Minute(0)),
            $weekDays
        );

        $openingHour2 = new OpeningHour(
            new OpeningTime(new Hour(13), new Minute(0)),
            new OpeningTime(new Hour(17), new Minute(0)),
            $weekDays
        );

        $weekendDays = (new DayOfWeekCollection())
            ->addDayOfWeek(DayOfWeek::SATURDAY())
            ->addDayOfWeek(DayOfWeek::SUNDAY());

        $openingHour3 = new OpeningHour(
            new OpeningTime(new Hour(10), new Minute(0)),
            new OpeningTime(new Hour(16), new Minute(0)),
            $weekendDays
        );

        $this->calendar = new Calendar(
            CalendarType::MULTIPLE(),
            DateTime::createFromFormat(DateTime::ATOM, self::START_DATE),
            DateTime::createFromFormat(DateTime::ATOM, self::END_DATE),
            [
                self::TIMESTAMP_1 => $timestamp1,
                self::TIMESTAMP_2 => $timestamp2,
            ],
            [
                $openingHour1,
                $openingHour2,
                $openingHour3,
            ]
        );
    }

    /**
     * @test
     */
    public function time_stamps_need_to_have_type_time_stamp()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Timestamps should have type TimeStamp.');

        new Calendar(
            CalendarType::SINGLE(),
            DateTime::createFromFormat(DateTime::ATOM, self::START_DATE),
            DateTime::createFromFormat(DateTime::ATOM, self::END_DATE),
            [
                'wrong timestamp',
            ]
        );
    }

    /**
     * @test
     */
    public function opening_hours_need_to_have_type_opening_hour()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('OpeningHours should have type OpeningHour.');

        new Calendar(
            CalendarType::PERIODIC(),
            DateTime::createFromFormat(DateTime::ATOM, self::START_DATE),
            DateTime::createFromFormat(DateTime::ATOM, self::END_DATE),
            [],
            [
                'wrong opening hours',
            ]
        );
    }

    /**
     * @test
     */
    public function it_can_serialize()
    {
        $this->assertEquals(
            [
                'type' => 'multiple',
                'startDate' => '2016-03-06T10:00:00+01:00',
                'endDate' => '2016-03-13T12:00:00+01:00',
                'timestamps' => [
                    [
                        'startDate' => self::TIMESTAMP_1_START_DATE,
                        'endDate' => self::TIMESTAMP_1_END_DATE,
                        'eventStatus' => 'EventScheduled',
                        'eventStatusReason' => [],
                    ],
                    [
                        'startDate' => self::TIMESTAMP_2_START_DATE,
                        'endDate' => self::TIMESTAMP_2_END_DATE,
                        'eventStatus' => 'EventScheduled',
                        'eventStatusReason' => [],
                    ],
                ],
                'openingHours' => [
                    [
                        'opens' => '09:00',
                        'closes' => '12:00',
                        'dayOfWeek' => [
                            'monday',
                            'tuesday',
                            'wednesday',
                            'thursday',
                            'friday',
                        ],
                    ],
                    [
                        'opens' => '13:00',
                        'closes' => '17:00',
                        'dayOfWeek' => [
                            'monday',
                            'tuesday',
                            'wednesday',
                            'thursday',
                            'friday',
                        ],
                    ],
                    [
                        'opens' => '10:00',
                        'closes' => '16:00',
                        'dayOfWeek' => [
                            'saturday',
                            'sunday',
                        ],
                    ],
                ],
            ],
            $this->calendar->serialize()
        );
    }

    /**
     * @test
     */
    public function it_can_deserialize()
    {
        $actualCalendar = Calendar::deserialize([
            'type' => 'multiple',
            'startDate' => '2016-03-06T10:00:00+01:00',
            'endDate' => '2016-03-13T12:00:00+01:00',
            'timestamps' => [
                [
                    'startDate' => self::TIMESTAMP_1_START_DATE,
                    'endDate' => self::TIMESTAMP_1_END_DATE,
                    'eventStatus' => 'EventScheduled',
                    'eventStatusReason' => [],
                ],
                [
                    'startDate' => self::TIMESTAMP_2_START_DATE,
                    'endDate' => self::TIMESTAMP_2_END_DATE,
                    'eventStatus' => 'EventScheduled',
                    'eventStatusReason' => [],
                ],
            ],
            'openingHours' => [
                [
                    'opens' => '09:00',
                    'closes' => '12:00',
                    'dayOfWeek' => [
                        'monday',
                        'tuesday',
                        'wednesday',
                        'thursday',
                        'friday',
                    ],
                ],
                [
                    'opens' => '13:00',
                    'closes' => '17:00',
                    'dayOfWeek' => [
                        'monday',
                        'tuesday',
                        'wednesday',
                        'thursday',
                        'friday',
                    ],
                ],
                [
                    'opens' => '10:00',
                    'closes' => '16:00',
                    'dayOfWeek' => [
                        'saturday',
                        'sunday',
                    ],
                ],
            ],
        ]);

        $this->assertEquals($this->calendar, $actualCalendar);
    }

    /**
     * @test
     * @dataProvider jsonldCalendarProvider
     */
    public function it_should_generate_the_expected_json_for_a_calendar_of_each_type(
        Calendar $calendar,
        array $jsonld
    ) {
        $this->assertEquals($jsonld, $calendar->toJsonLd());
    }

    /**
     * @return array
     */
    public function jsonldCalendarProvider()
    {
        return [
            'single' => [
                'calendar' => new Calendar(
                    CalendarType::SINGLE(),
                    null,
                    null,
                    [
                        new Timestamp(
                            DateTime::createFromFormat(DateTime::ATOM, '2016-03-06T10:00:00+01:00'),
                            DateTime::createFromFormat(DateTime::ATOM, '2016-03-13T12:00:00+01:00')
                        ),
                    ]
                ),
                'jsonld' => [
                    'calendarType' => 'single',
                    'startDate' => '2016-03-06T10:00:00+01:00',
                    'endDate' => '2016-03-13T12:00:00+01:00',
                    'subEvent' => [
                        [
                            '@type' => 'Event',
                            'startDate' => '2016-03-06T10:00:00+01:00',
                            'endDate' => '2016-03-13T12:00:00+01:00',
                            'eventStatus' => 'EventScheduled',
                            'eventStatusReason' => [],
                        ],
                    ],
                ],
            ],
            'multiple' => [
                'calendar' => new Calendar(
                    CalendarType::MULTIPLE(),
                    null,
                    null,
                    [
                        new Timestamp(
                            DateTime::createFromFormat(DateTime::ATOM, '2016-03-06T10:00:00+01:00'),
                            DateTime::createFromFormat(DateTime::ATOM, '2016-03-13T12:00:00+01:00')
                        ),
                        new Timestamp(
                            DateTime::createFromFormat(DateTime::ATOM, '2020-03-06T10:00:00+01:00'),
                            DateTime::createFromFormat(DateTime::ATOM, '2020-03-13T12:00:00+01:00')
                        ),
                    ]
                ),
                'jsonld' => [
                    'calendarType' => 'multiple',
                    'startDate' => '2016-03-06T10:00:00+01:00',
                    'endDate' => '2020-03-13T12:00:00+01:00',
                    'subEvent' => [
                        [
                            '@type' => 'Event',
                            'startDate' => '2016-03-06T10:00:00+01:00',
                            'endDate' => '2016-03-13T12:00:00+01:00',
                            'eventStatus' => 'EventScheduled',
                            'eventStatusReason' => [],
                        ],
                        [
                            '@type' => 'Event',
                            'startDate' => '2020-03-06T10:00:00+01:00',
                            'endDate' => '2020-03-13T12:00:00+01:00',
                            'eventStatus' => 'EventScheduled',
                            'eventStatusReason' => [],
                        ],
                    ],
                ],
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
                ],
            ],
            'permanent' => [
                'calendar' => new Calendar(
                    CalendarType::PERMANENT()
                ),
                'jsonld' => [
                    'calendarType' => 'permanent',
                ],
            ],
        ];
    }

    /**
     * @test
     */
    public function it_should_assume_the_timezone_is_Brussels_when_none_is_provided_when_deserializing()
    {
        $oldCalendarData = [
            'type' => 'periodic',
            'startDate' => '2016-03-06T10:00:00',
            'endDate' => '2016-03-13T12:00:00',
            'timestamps' => [],
        ];

        $expectedCalendar = new Calendar(
            CalendarType::PERIODIC(),
            DateTime::createFromFormat(DateTime::ATOM, self::START_DATE),
            DateTime::createFromFormat(DateTime::ATOM, self::END_DATE)
        );

        $calendar = Calendar::deserialize($oldCalendarData);

        $this->assertEquals($expectedCalendar, $calendar);
    }

    /**
     * @test
     */
    public function it_throws_invalid_argument_exception_when_start_date_can_not_be_converted()
    {
        $invalidCalendarData = [
            'type' => 'single',
            'startDate' => '2016-03-06 10:00:00',
            'endDate' => '2016-03-13T12:00:00',
            'timestamps' => [],
        ];

        $this->expectException(\InvalidArgumentException::class);

        Calendar::deserialize($invalidCalendarData);
    }

    /**
     * @test
     */
    public function it_throws_invalid_argument_exception_when_end_date_can_not_be_converted()
    {
        $invalidCalendarData = [
            'type' => 'single',
            'startDate' => '2016-03-06T10:00:00',
            'endDate' => '2016-03-13 12:00:00',
            'timestamps' => [],
        ];

        $this->expectException(\InvalidArgumentException::class);

        Calendar::deserialize($invalidCalendarData);
    }

    /**
     * @test
     */
    public function it_adds_a_timestamp_based_on_start_and_end_date_if_one_is_missing_for_single_calendars()
    {
        $invalidCalendarData = [
            'type' => 'single',
            'startDate' => '2016-03-06T10:00:00',
            'endDate' => '2016-03-13T12:00:00',
            'timestamps' => [],
        ];

        $expected = new Calendar(
            CalendarType::SINGLE(),
            DateTime::createFromFormat(DateTime::ATOM, '2016-03-06T10:00:00+01:00'),
            DateTime::createFromFormat(DateTime::ATOM, '2016-03-13T12:00:00+01:00'),
            [
                new Timestamp(
                    DateTime::createFromFormat(DateTime::ATOM, '2016-03-06T10:00:00+01:00'),
                    DateTime::createFromFormat(DateTime::ATOM, '2016-03-13T12:00:00+01:00')
                ),
            ]
        );

        $actual = Calendar::deserialize($invalidCalendarData);

        $this->assertEquals($expected, $actual);
    }

    /**
     * @test
     */
    public function it_adds_a_timestamp_based_on_start_date_if_one_is_missing_for_single_calendars()
    {
        $invalidCalendarData = [
            'type' => 'single',
            'startDate' => '2016-03-06T10:00:00',
            'endDate' => null,
            'timestamps' => [],
        ];

        $expected = new Calendar(
            CalendarType::SINGLE(),
            DateTime::createFromFormat(DateTime::ATOM, '2016-03-06T10:00:00+01:00'),
            null,
            [
                new Timestamp(
                    DateTime::createFromFormat(DateTime::ATOM, '2016-03-06T10:00:00+01:00'),
                    DateTime::createFromFormat(DateTime::ATOM, '2016-03-06T10:00:00+01:00')
                ),
            ]
        );

        $actual = Calendar::deserialize($invalidCalendarData);

        $this->assertEquals($expected, $actual);
    }

    /**
     * @test
     */
    public function it_adds_a_timestamp_based_on_start_and_end_date_if_one_is_missing_for_multiple_calendars()
    {
        $invalidCalendarData = [
            'type' => 'multiple',
            'startDate' => '2016-03-06T10:00:00',
            'endDate' => '2016-03-13T12:00:00',
            'timestamps' => [],
        ];

        $expected = new Calendar(
            CalendarType::MULTIPLE(),
            DateTime::createFromFormat(DateTime::ATOM, '2016-03-06T10:00:00+01:00'),
            DateTime::createFromFormat(DateTime::ATOM, '2016-03-13T12:00:00+01:00'),
            [
                new Timestamp(
                    DateTime::createFromFormat(DateTime::ATOM, '2016-03-06T10:00:00+01:00'),
                    DateTime::createFromFormat(DateTime::ATOM, '2016-03-13T12:00:00+01:00')
                ),
            ]
        );

        $actual = Calendar::deserialize($invalidCalendarData);

        $this->assertEquals($expected, $actual);
    }

    /**
     * @test
     */
    public function it_adds_a_timestamp_based_on_start_date_if_one_is_missing_for_multiple_calendars()
    {
        $invalidCalendarData = [
            'type' => 'multiple',
            'startDate' => '2016-03-06T10:00:00',
            'endDate' => null,
            'timestamps' => [],
        ];

        $expected = new Calendar(
            CalendarType::MULTIPLE(),
            DateTime::createFromFormat(DateTime::ATOM, '2016-03-06T10:00:00+01:00'),
            null,
            [
                new Timestamp(
                    DateTime::createFromFormat(DateTime::ATOM, '2016-03-06T10:00:00+01:00'),
                    DateTime::createFromFormat(DateTime::ATOM, '2016-03-06T10:00:00+01:00')
                ),
            ]
        );

        $actual = Calendar::deserialize($invalidCalendarData);

        $this->assertEquals($expected, $actual);
    }

    /**
     * @test
     * @dataProvider periodicCalendarWithMissingDatesDataProvider
     */
    public function it_should_not_create_a_periodic_calendar_with_missing_dates(array $calendarData)
    {
        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessage('A period should have a start- and end-date.');

        Calendar::deserialize($calendarData);
    }

    public function periodicCalendarWithMissingDatesDataProvider()
    {
        return [
            'no dates' => [
                'calendarData' => [
                    'type' => 'periodic',
                ],
            ],
            'start date missing' => [
                'calendarData' => [
                    'type' => 'periodic',
                    'endDate' => '2016-03-13T12:00:00',
                ],
            ],
            'end date missing' => [
                'calendarData' => [
                    'type' => 'periodic',
                    'startDate' => '2016-03-06T10:00:00',
                ],
            ],
        ];
    }

    /**
     * @test
     */
    public function it_should_be_creatable_from_an_udb3_model_single_date_range_calendar()
    {
        $dateRange = new DateRange(
            \DateTimeImmutable::createFromFormat(\DATE_ATOM, '2016-03-06T10:00:00+01:00'),
            \DateTimeImmutable::createFromFormat(\DATE_ATOM, '2016-03-07T10:00:00+01:00')
        );

        $udb3ModelCalendar = new SingleDateRangeCalendar($dateRange);

        $expected = new Calendar(
            CalendarType::SINGLE(),
            \DateTimeImmutable::createFromFormat(\DATE_ATOM, '2016-03-06T10:00:00+01:00'),
            \DateTimeImmutable::createFromFormat(\DATE_ATOM, '2016-03-07T10:00:00+01:00'),
            [
                new Timestamp(
                    \DateTimeImmutable::createFromFormat(\DATE_ATOM, '2016-03-06T10:00:00+01:00'),
                    \DateTimeImmutable::createFromFormat(\DATE_ATOM, '2016-03-07T10:00:00+01:00')
                ),
            ],
            []
        );

        $actual = Calendar::fromUdb3ModelCalendar($udb3ModelCalendar);

        $this->assertEquals($expected, $actual);
    }

    /**
     * @test
     */
    public function it_should_be_creatable_from_an_udb3_model_multiple_date_range_calendar()
    {
        $dateRanges = new DateRanges(
            new DateRange(
                \DateTimeImmutable::createFromFormat(\DATE_ATOM, '2016-03-06T10:00:00+01:00'),
                \DateTimeImmutable::createFromFormat(\DATE_ATOM, '2016-03-07T10:00:00+01:00')
            ),
            new DateRange(
                \DateTimeImmutable::createFromFormat(\DATE_ATOM, '2016-03-09T10:00:00+01:00'),
                \DateTimeImmutable::createFromFormat(\DATE_ATOM, '2016-03-10T10:00:00+01:00')
            )
        );

        $udb3ModelCalendar = new MultipleDateRangesCalendar($dateRanges);

        $expected = new Calendar(
            CalendarType::MULTIPLE(),
            \DateTimeImmutable::createFromFormat(\DATE_ATOM, '2016-03-06T10:00:00+01:00'),
            \DateTimeImmutable::createFromFormat(\DATE_ATOM, '2016-03-10T10:00:00+01:00'),
            [
                new Timestamp(
                    \DateTimeImmutable::createFromFormat(\DATE_ATOM, '2016-03-06T10:00:00+01:00'),
                    \DateTimeImmutable::createFromFormat(\DATE_ATOM, '2016-03-07T10:00:00+01:00')
                ),
                new Timestamp(
                    \DateTimeImmutable::createFromFormat(\DATE_ATOM, '2016-03-09T10:00:00+01:00'),
                    \DateTimeImmutable::createFromFormat(\DATE_ATOM, '2016-03-10T10:00:00+01:00')
                ),
            ],
            []
        );

        $actual = Calendar::fromUdb3ModelCalendar($udb3ModelCalendar);

        $this->assertEquals($expected, $actual);
    }

    /**
     * @test
     */
    public function it_should_be_creatable_from_an_udb3_model_periodic_calendar()
    {
        $dateRange = new DateRange(
            \DateTimeImmutable::createFromFormat(\DATE_ATOM, '2016-03-06T10:00:00+01:00'),
            \DateTimeImmutable::createFromFormat(\DATE_ATOM, '2016-03-07T10:00:00+01:00')
        );

        $openingHours = new OpeningHours();

        $udb3ModelCalendar = new PeriodicCalendar($dateRange, $openingHours);

        $expected = new Calendar(
            CalendarType::PERIODIC(),
            \DateTimeImmutable::createFromFormat(\DATE_ATOM, '2016-03-06T10:00:00+01:00'),
            \DateTimeImmutable::createFromFormat(\DATE_ATOM, '2016-03-07T10:00:00+01:00'),
            [],
            []
        );

        $actual = Calendar::fromUdb3ModelCalendar($udb3ModelCalendar);

        $this->assertEquals($expected, $actual);
    }

    /**
     * @test
     */
    public function it_should_be_creatable_from_an_udb3_model_periodic_calendar_with_opening_hours()
    {
        $dateRange = new DateRange(
            \DateTimeImmutable::createFromFormat(\DATE_ATOM, '2016-03-06T10:00:00+01:00'),
            \DateTimeImmutable::createFromFormat(\DATE_ATOM, '2016-03-07T10:00:00+01:00')
        );

        $openingHours = new OpeningHours(
            new Udb3ModelOpeningHour(
                new Days(
                    Day::monday(),
                    Day::tuesday()
                ),
                new Time(
                    new Udb3ModelHour(8),
                    new Udb3ModelMinute(0)
                ),
                new Time(
                    new Udb3ModelHour(12),
                    new Udb3ModelMinute(59)
                )
            ),
            new Udb3ModelOpeningHour(
                new Days(
                    Day::saturday()
                ),
                new Time(
                    new Udb3ModelHour(10),
                    new Udb3ModelMinute(0)
                ),
                new Time(
                    new Udb3ModelHour(14),
                    new Udb3ModelMinute(0)
                )
            )
        );

        $udb3ModelCalendar = new PeriodicCalendar($dateRange, $openingHours);

        $expected = new Calendar(
            CalendarType::PERIODIC(),
            \DateTimeImmutable::createFromFormat(\DATE_ATOM, '2016-03-06T10:00:00+01:00'),
            \DateTimeImmutable::createFromFormat(\DATE_ATOM, '2016-03-07T10:00:00+01:00'),
            [],
            [
                new OpeningHour(
                    new OpeningTime(new Hour(8), new Minute(0)),
                    new OpeningTime(new Hour(12), new Minute(59)),
                    new DayOfWeekCollection(
                        DayOfWeek::MONDAY(),
                        DayOfWeek::TUESDAY()
                    )
                ),
                new OpeningHour(
                    new OpeningTime(new Hour(10), new Minute(0)),
                    new OpeningTime(new Hour(14), new Minute(0)),
                    new DayOfWeekCollection(
                        DayOfWeek::SATURDAY()
                    )
                ),
            ]
        );

        $actual = Calendar::fromUdb3ModelCalendar($udb3ModelCalendar);

        $this->assertEquals($expected, $actual);
    }

    /**
     * @test
     */
    public function it_should_be_creatable_from_an_udb3_model_permanent_calendar()
    {
        $openingHours = new OpeningHours();
        $udb3ModelCalendar = new PermanentCalendar($openingHours);

        $expected = new Calendar(
            CalendarType::PERMANENT(),
            null,
            null,
            [],
            []
        );

        $actual = Calendar::fromUdb3ModelCalendar($udb3ModelCalendar);

        $this->assertEquals($expected, $actual);
    }

    /**
     * @test
     */
    public function it_should_be_creatable_from_an_udb3_model_permanent_calendar_with_opening_hours()
    {
        $openingHours = new OpeningHours(
            new Udb3ModelOpeningHour(
                new Days(
                    Day::monday(),
                    Day::tuesday()
                ),
                new Time(
                    new Udb3ModelHour(8),
                    new Udb3ModelMinute(0)
                ),
                new Time(
                    new Udb3ModelHour(12),
                    new Udb3ModelMinute(59)
                )
            ),
            new Udb3ModelOpeningHour(
                new Days(
                    Day::saturday()
                ),
                new Time(
                    new Udb3ModelHour(10),
                    new Udb3ModelMinute(0)
                ),
                new Time(
                    new Udb3ModelHour(14),
                    new Udb3ModelMinute(0)
                )
            )
        );

        $udb3ModelCalendar = new PermanentCalendar($openingHours);

        $expected = new Calendar(
            CalendarType::PERMANENT(),
            null,
            null,
            [],
            [
                new OpeningHour(
                    new OpeningTime(new Hour(8), new Minute(0)),
                    new OpeningTime(new Hour(12), new Minute(59)),
                    new DayOfWeekCollection(
                        DayOfWeek::MONDAY(),
                        DayOfWeek::TUESDAY()
                    )
                ),
                new OpeningHour(
                    new OpeningTime(new Hour(10), new Minute(0)),
                    new OpeningTime(new Hour(14), new Minute(0)),
                    new DayOfWeekCollection(
                        DayOfWeek::SATURDAY()
                    )
                ),
            ]
        );

        $actual = Calendar::fromUdb3ModelCalendar($udb3ModelCalendar);

        $this->assertEquals($expected, $actual);
    }

    /**
     * @test
     */
    public function it_can_determine_same_calendars()
    {
        $calendar = new Calendar(
            CalendarType::PERIODIC(),
            \DateTime::createFromFormat(\DateTime::ATOM, '2020-01-26T11:11:11+01:00'),
            \DateTime::createFromFormat(\DateTime::ATOM, '2020-01-27T12:12:12+01:00')
        );

        $sameCalendar = new Calendar(
            CalendarType::PERIODIC(),
            \DateTime::createFromFormat(\DateTime::ATOM, '2020-01-26T11:11:11+01:00'),
            \DateTime::createFromFormat(\DateTime::ATOM, '2020-01-27T12:12:12+01:00')
        );

        $otherCalendar = new Calendar(
            CalendarType::PERIODIC(),
            \DateTime::createFromFormat(\DateTime::ATOM, '2020-01-27T11:11:11+01:00'),
            \DateTime::createFromFormat(\DateTime::ATOM, '2020-01-28T12:12:12+01:00')
        );

        $this->assertTrue($calendar->sameAs($sameCalendar));
        $this->assertFalse($calendar->sameAs($otherCalendar));
    }

    /**
     * @test
     */
    public function it_should_return_timestamps_in_chronological_order(): void
    {
        $calendar = new Calendar(
            CalendarType::MULTIPLE(),
            DateTime::createFromFormat(\DateTime::ATOM, '2020-04-01T11:11:11+01:00'),
            DateTime::createFromFormat(\DateTime::ATOM, '2020-04-30T12:12:12+01:00'),
            [
                new Timestamp(
                    DateTime::createFromFormat(\DateTime::ATOM, '2020-04-05T11:11:11+01:00'),
                    DateTime::createFromFormat(\DateTime::ATOM, '2020-04-10T12:12:12+01:00')
                ),
                new Timestamp(
                    DateTime::createFromFormat(\DateTime::ATOM, '2020-04-07T11:11:11+01:00'),
                    DateTime::createFromFormat(\DateTime::ATOM, '2020-04-09T12:12:12+01:00')
                ),
                new Timestamp(
                    DateTime::createFromFormat(\DateTime::ATOM, '2020-04-15T11:11:11+01:00'),
                    DateTime::createFromFormat(\DateTime::ATOM, '2020-04-25T12:12:12+01:00')
                ),
                new Timestamp(
                    DateTime::createFromFormat(\DateTime::ATOM, '2020-04-01T11:11:11+01:00'),
                    DateTime::createFromFormat(\DateTime::ATOM, '2020-04-20T12:12:12+01:00')
                ),
            ]
        );

        $expected = [
            new Timestamp(
                DateTime::createFromFormat(\DateTime::ATOM, '2020-04-01T11:11:11+01:00'),
                DateTime::createFromFormat(\DateTime::ATOM, '2020-04-20T12:12:12+01:00')
            ),
            new Timestamp(
                DateTime::createFromFormat(\DateTime::ATOM, '2020-04-05T11:11:11+01:00'),
                DateTime::createFromFormat(\DateTime::ATOM, '2020-04-10T12:12:12+01:00')
            ),
            new Timestamp(
                DateTime::createFromFormat(\DateTime::ATOM, '2020-04-07T11:11:11+01:00'),
                DateTime::createFromFormat(\DateTime::ATOM, '2020-04-09T12:12:12+01:00')
            ),
            new Timestamp(
                DateTime::createFromFormat(\DateTime::ATOM, '2020-04-15T11:11:11+01:00'),
                DateTime::createFromFormat(\DateTime::ATOM, '2020-04-25T12:12:12+01:00')
            ),
        ];

        $this->assertEquals(
            $expected,
            $calendar->getTimestamps()
        );
    }
}
