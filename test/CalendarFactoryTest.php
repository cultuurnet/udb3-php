<?php

namespace CultuurNet\UDB3;

use CultureFeed_Cdb_Data_Calendar_Timestamp;
use CultureFeed_Cdb_Data_Calendar_TimestampList;
use CultuurNet\UDB3\Calendar\DayOfWeek;
use CultuurNet\UDB3\Calendar\DayOfWeekCollection;
use CultuurNet\UDB3\Calendar\OpeningHour;
use CultuurNet\UDB3\Calendar\OpeningTime;
use DateTimeImmutable;
use DateTimeZone;
use PHPUnit_Framework_TestCase;
use ValueObjects\DateTime\Hour;
use ValueObjects\DateTime\Minute;

class CalendarFactoryTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var CalendarFactory
     */
    private $factory;

    public function setUp()
    {
        $this->factory = new CalendarFactory();
    }

    /**
     * @test
     */
    public function it_drops_timestamp_timeend_before_timestart()
    {
        $cdbCalendar = new CultureFeed_Cdb_Data_Calendar_TimestampList();
        $cdbCalendar->add(
            new CultureFeed_Cdb_Data_Calendar_Timestamp(
                "2016-12-16",
                "21:00:00",
                "05:00:00"
            )
        );

        $calendar = $this->factory->createFromCdbCalendar($cdbCalendar);

        $expectedTimeZone = new DateTimeZone('Europe/Brussels');
        $expectedStartDate = $expectedEndDate = new DateTimeImmutable(
            '2016-12-16 21:00:00',
            $expectedTimeZone
        );

        $expectedCalendar = new Calendar(
            CalendarType::SINGLE(),
            $expectedStartDate,
            $expectedEndDate,
            [
                new Timestamp($expectedStartDate, $expectedEndDate),
            ]
        );

        $this->assertEquals($expectedCalendar, $calendar);
    }

    /**
     * @test
     */
    public function it_can_create_a_calendar_from_a_weekscheme()
    {
        $weekDays = new DayOfWeekCollection(
            DayOfWeek::MONDAY(),
            DayOfWeek::TUESDAY(),
            DayOfWeek::WEDNESDAY(),
            DayOfWeek::THURSDAY(),
            DayOfWeek::FRIDAY()
        );

        $weekendDays = new DayOfWeekCollection(
            DayOfWeek::SATURDAY(),
            DayOfWeek::SUNDAY()
        );

        $expectedCalendar = new Calendar(
            CalendarType::PERMANENT(),
            null,
            null,
            [],
            [
                new OpeningHour(
                    new OpeningTime(new Hour(9), new Minute(0)),
                    new OpeningTime(new Hour(12), new Minute(0)),
                    $weekDays
                ),
                new OpeningHour(
                    new OpeningTime(new Hour(13), new Minute(0)),
                    new OpeningTime(new Hour(17), new Minute(0)),
                    $weekDays
                ),
                new OpeningHour(
                    new OpeningTime(new Hour(10), new Minute(0)),
                    new OpeningTime(new Hour(16), new Minute(0)),
                    $weekendDays
                ),
            ]
        );

        $weekScheme = \CultureFeed_Cdb_Data_Calendar_Weekscheme::parseFromCdbXml(
            simplexml_load_file(__DIR__ . '/week_scheme.xml')
        );

        $calendar = $this->factory->createFromWeekScheme($weekScheme);

        $this->assertEquals($expectedCalendar, $calendar);
    }

    /**
     * @scenario import event with one timestamp: date + timestart, no timeend
     * @dataProvider timestampListDataProvider
     * @test
     */
    public function it_creates_calendars_with_timestamps_from_a_cdb_timestamp_list(
        CultureFeed_Cdb_Data_Calendar_TimestampList $cdbCalendar,
        Calendar $expectedCalendar
    ) {
        $calendar = $this->factory->createFromCdbCalendar($cdbCalendar);
        $this->assertEquals($expectedCalendar, $calendar);
    }

    public function timestampListDataProvider()
    {
        $timeZone = new DateTimeZone('Europe/Brussels');

        return [
            'import event with one timestamp: date, no timestart or timeend' => [
                'cdbCalendar' => (function () {
                    $cdbCalendar = new CultureFeed_Cdb_Data_Calendar_TimestampList();
                    $cdbCalendar->add(
                        new CultureFeed_Cdb_Data_Calendar_Timestamp(
                            "2017-05-21",
                            "00:00:00",
                            "23:59:00"
                        )
                    );
                    return $cdbCalendar;
                })(),
                'expectedCalendar' => new Calendar(
                    CalendarType::SINGLE(),
                    new DateTimeImmutable(
                        '2017-05-21 00:00:00',
                        $timeZone
                    ),
                    new DateTimeImmutable(
                        '2017-05-21 23:59:00',
                        $timeZone
                    ),
                    [
                        new Timestamp(
                            new DateTimeImmutable(
                                '2017-05-21 00:00:00',
                                $timeZone
                            ),
                            new DateTimeImmutable(
                                '2017-05-21 23:59:00',
                                $timeZone
                            )
                        ),
                    ]
                )
            ],
            'import event with one timestamp: date + timestart, no timeend' => [
                'cdbCalendar' => (function () {
                    $cdbCalendar = new CultureFeed_Cdb_Data_Calendar_TimestampList();
                    $cdbCalendar->add(
                        new CultureFeed_Cdb_Data_Calendar_Timestamp(
                            "2017-05-21",
                            "10:00:00",
                            "10:00:00"
                        )
                    );
                    return $cdbCalendar;
                })(),
                'expectedCalendar' => new Calendar(
                    CalendarType::SINGLE(),
                    new DateTimeImmutable(
                        '2017-05-21 10:00:00',
                        $timeZone
                    ),
                    new DateTimeImmutable(
                        '2017-05-21 10:00:00',
                        $timeZone
                    ),
                    [
                        new Timestamp(
                            new DateTimeImmutable(
                                '2017-05-21 10:00:00',
                                $timeZone
                            ),
                            new DateTimeImmutable(
                                '2017-05-21 10:00:00',
                                $timeZone
                            )
                        ),
                    ]
                )
            ],
            'import event with one timestamp: date + timestart + timeend' => [
                'cdbCalendar' => (function () {
                    $cdbCalendar = new CultureFeed_Cdb_Data_Calendar_TimestampList();
                    $cdbCalendar->add(
                        new CultureFeed_Cdb_Data_Calendar_Timestamp(
                            "2017-05-21",
                            "10:00:00",
                            "11:00:00"
                        )
                    );
                    return $cdbCalendar;
                })(),
                'expectedCalendar' => new Calendar(
                    CalendarType::SINGLE(),
                    new DateTimeImmutable(
                        '2017-05-21 10:00:00',
                        $timeZone
                    ),
                    new DateTimeImmutable(
                        '2017-05-21 11:00:00',
                        $timeZone
                    ),
                    [
                        new Timestamp(
                            new DateTimeImmutable(
                                '2017-05-21 10:00:00',
                                $timeZone
                            ),
                            new DateTimeImmutable(
                                '2017-05-21 11:00:00',
                                $timeZone
                            )
                        ),
                    ]
                )
            ],
            'import event with multiple timestamps: dates, no timestart or timeend' => [
                'cdbCalendar' => (function () {
                    $cdbCalendar = new CultureFeed_Cdb_Data_Calendar_TimestampList();
                    $cdbCalendar->add(
                        new CultureFeed_Cdb_Data_Calendar_Timestamp(
                            "2017-05-21",
                            "00:00:00",
                            "23:59:00"
                        )
                    );
                    $cdbCalendar->add(
                        new CultureFeed_Cdb_Data_Calendar_Timestamp(
                            "2017-06-21",
                            "00:00:00",
                            "23:59:00"
                        )
                    );
                    $cdbCalendar->add(
                        new CultureFeed_Cdb_Data_Calendar_Timestamp(
                            "2017-07-21",
                            "00:00:00",
                            "23:59:00"
                        )
                    );
                    return $cdbCalendar;
                })(),
                'expectedCalendar' => new Calendar(
                    CalendarType::MULTIPLE(),
                    new DateTimeImmutable(
                        '2017-05-21 00:00:00',
                        $timeZone
                    ),
                    new DateTimeImmutable(
                        '2017-07-21 23:59:00',
                        $timeZone
                    ),
                    [
                        new Timestamp(
                            new DateTimeImmutable(
                                '2017-05-21 00:00:00',
                                $timeZone
                            ),
                            new DateTimeImmutable(
                                '2017-05-21 23:59:00',
                                $timeZone
                            )
                        ),
                        new Timestamp(
                            new DateTimeImmutable(
                                '2017-06-21 00:00:00',
                                $timeZone
                            ),
                            new DateTimeImmutable(
                                '2017-06-21 23:59:00',
                                $timeZone
                            )
                        ),
                        new Timestamp(
                            new DateTimeImmutable(
                                '2017-07-21 00:00:00',
                                $timeZone
                            ),
                            new DateTimeImmutable(
                                '2017-07-21 23:59:00',
                                $timeZone
                            )
                        ),
                    ]
                )
            ],
            'import event with multiple timestamps: dates + timestart, no timeend' => [
                'cdbCalendar' => (function () {
                    $cdbCalendar = new CultureFeed_Cdb_Data_Calendar_TimestampList();
                    $cdbCalendar->add(
                        new CultureFeed_Cdb_Data_Calendar_Timestamp(
                            "2017-05-21",
                            "10:00:00",
                            "10:00:00"
                        )
                    );
                    $cdbCalendar->add(
                        new CultureFeed_Cdb_Data_Calendar_Timestamp(
                            "2017-06-21",
                            "10:00:00",
                            "10:00:00"
                        )
                    );
                    return $cdbCalendar;
                })(),
                'expectedCalendar' => new Calendar(
                    CalendarType::MULTIPLE(),
                    new DateTimeImmutable(
                        '2017-05-21 10:00:00',
                        $timeZone
                    ),
                    new DateTimeImmutable(
                        '2017-06-21 10:00:00',
                        $timeZone
                    ),
                    [
                        new Timestamp(
                            new DateTimeImmutable(
                                '2017-05-21 10:00:00',
                                $timeZone
                            ),
                            new DateTimeImmutable(
                                '2017-05-21 10:00:00',
                                $timeZone
                            )
                        ),
                        new Timestamp(
                            new DateTimeImmutable(
                                '2017-06-21 10:00:00',
                                $timeZone
                            ),
                            new DateTimeImmutable(
                                '2017-06-21 10:00:00',
                                $timeZone
                            )
                        ),
                    ]
                )
            ],
            'import event with multiple timestamps: dates + timestart + timeend' => [
                'cdbCalendar' => (function () {
                    $cdbCalendar = new CultureFeed_Cdb_Data_Calendar_TimestampList();
                    $cdbCalendar->add(
                        new CultureFeed_Cdb_Data_Calendar_Timestamp(
                            "2017-05-21",
                            "10:00:00",
                            "11:30:00"
                        )
                    );
                    $cdbCalendar->add(
                        new CultureFeed_Cdb_Data_Calendar_Timestamp(
                            "2017-06-21",
                            "10:00:00",
                            "11:30:00"
                        )
                    );
                    return $cdbCalendar;
                })(),
                'expectedCalendar' => new Calendar(
                    CalendarType::MULTIPLE(),
                    new DateTimeImmutable(
                        '2017-05-21 10:00:00',
                        $timeZone
                    ),
                    new DateTimeImmutable(
                        '2017-06-21 11:30:00',
                        $timeZone
                    ),
                    [
                        new Timestamp(
                            new DateTimeImmutable(
                                '2017-05-21 10:00:00',
                                $timeZone
                            ),
                            new DateTimeImmutable(
                                '2017-05-21 11:30:00',
                                $timeZone
                            )
                        ),
                        new Timestamp(
                            new DateTimeImmutable(
                                '2017-06-21 10:00:00',
                                $timeZone
                            ),
                            new DateTimeImmutable(
                                '2017-06-21 11:30:00',
                                $timeZone
                            )
                        ),
                    ]
                )
            ],
            'import event with multiple timestamps: mixed: dates, with or without timestart/timeend' => [
                'cdbCalendar' => (function () {
                    $cdbCalendar = new CultureFeed_Cdb_Data_Calendar_TimestampList();
                    $cdbCalendar->add(
                        new CultureFeed_Cdb_Data_Calendar_Timestamp(
                            "2017-05-21",
                            "00:00:00",
                            "23:59:00"
                        )
                    );
                    $cdbCalendar->add(
                        new CultureFeed_Cdb_Data_Calendar_Timestamp(
                            "2017-06-21",
                            "10:00:00",
                            "10:00:00"
                        )
                    );
                    $cdbCalendar->add(
                        new CultureFeed_Cdb_Data_Calendar_Timestamp(
                            "2017-07-21",
                            "10:00:00",
                            "11:30:00"
                        )
                    );
                    return $cdbCalendar;
                })(),
                'expectedCalendar' => new Calendar(
                    CalendarType::MULTIPLE(),
                    new DateTimeImmutable(
                        '2017-05-21 00:00:00',
                        $timeZone
                    ),
                    new DateTimeImmutable(
                        '2017-07-21 11:30:00',
                        $timeZone
                    ),
                    [
                        new Timestamp(
                            new DateTimeImmutable(
                                '2017-05-21 00:00:00',
                                $timeZone
                            ),
                            new DateTimeImmutable(
                                '2017-05-21 23:59:00',
                                $timeZone
                            )
                        ),
                        new Timestamp(
                            new DateTimeImmutable(
                                '2017-06-21 10:00:00',
                                $timeZone
                            ),
                            new DateTimeImmutable(
                                '2017-06-21 10:00:00',
                                $timeZone
                            )
                        ),
                        new Timestamp(
                            new DateTimeImmutable(
                                '2017-07-21 10:00:00',
                                $timeZone
                            ),
                            new DateTimeImmutable(
                                '2017-07-21 11:30:00',
                                $timeZone
                            )
                        ),
                    ]
                )
            ],
        ];
    }
}
