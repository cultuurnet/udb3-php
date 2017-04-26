<?php

namespace CultuurNet\UDB3;

use CultuurNet\UDB3\Calendar\DayOfWeek;
use CultuurNet\UDB3\Calendar\OpeningHour;
use CultuurNet\UDB3\Calendar\OpeningTime;
use CultuurNet\UDB3\Cdb\DateTimeFactory;

class CalendarFactory implements CalendarFactoryInterface
{
    /**
     * @inheritdoc
     */
    public function createFromCdbCalendar(\CultureFeed_Cdb_Data_Calendar $cdbCalendar)
    {
        //
        // Get the calendar type.
        //
        $calendarType = '';
        if ($cdbCalendar instanceof \CultureFeed_Cdb_Data_Calendar_Permanent) {
            $calendarType = 'permanent';
        } else if ($cdbCalendar instanceof \CultureFeed_Cdb_Data_Calendar_PeriodList) {
            $calendarType = 'periodic';
        } else if ($cdbCalendar instanceof \CultureFeed_Cdb_Data_Calendar_TimestampList) {
            $calendarType = 'single';
            if (iterator_count($cdbCalendar) > 1) {
                $calendarType = 'multiple';
            }
        }

        //
        // Get the start day.
        //
        $cdbCalendar->rewind();
        $startDateString = '';
        if ($cdbCalendar instanceof \CultureFeed_Cdb_Data_Calendar_PeriodList) {
            /** @var \CultureFeed_Cdb_Data_Calendar_Period $period */
            $period = $cdbCalendar->current();
            $startDateString = $period->getDateFrom() . 'T00:00:00';
        } else if ($cdbCalendar instanceof \CultureFeed_Cdb_Data_Calendar_TimestampList) {
            /** @var \CultureFeed_Cdb_Data_Calendar_Timestamp $timestamp */
            $timestamp = $cdbCalendar->current();
            if ($timestamp->getStartTime()) {
                $startDateString = $timestamp->getDate() . 'T' . $timestamp->getStartTime();
            } else {
                $startDateString = $timestamp->getDate() . 'T00:00:00';
            }
        }
        $startDate = !empty($startDateString) ? DateTimeFactory::dateTimeFromDateString($startDateString) : null;

        //
        // Get the end day.
        //
        $cdbCalendar->rewind();
        $endDateString = '';
        if ($cdbCalendar instanceof \CultureFeed_Cdb_Data_Calendar_PeriodList) {
            /** @var \CultureFeed_Cdb_Data_Calendar_Period $period */
            $period = $cdbCalendar->current();
            $endDateString = $period->getDateTo() . 'T00:00:00';
        } else if ($cdbCalendar instanceof \CultureFeed_Cdb_Data_Calendar_TimestampList) {
            $firstTimestamp = $cdbCalendar->current();
            /** @var \CultureFeed_Cdb_Data_Calendar_Timestamp $timestamp */
            $cdbCalendarAsArray = iterator_to_array($cdbCalendar);
            $timestamp = iterator_count($cdbCalendar) > 1 ? end($cdbCalendarAsArray) : $firstTimestamp;
            if ($timestamp->getEndTime()) {
                $endDateString = $timestamp->getDate() . 'T' . $timestamp->getEndTime();
            } else {
                $endTime = $timestamp->getStartTime() ? $timestamp->getStartTime() : '00:00:00';
                $endDateString = $timestamp->getDate() . 'T' . $endTime;
            }
        }
        $endDate = !empty($endDateString) ? DateTimeFactory::dateTimeFromDateString($endDateString) : null;

        //
        // Get the time stamps.
        //
        $cdbCalendar->rewind();
        $timestamps = [];
        if ($cdbCalendar instanceof \CultureFeed_Cdb_Data_Calendar_TimestampList) {
            while ($cdbCalendar->valid()) {
                /** @var \CultureFeed_Cdb_Data_Calendar_Timestamp $timestamp */
                $timestamp = $cdbCalendar->current();
                $cdbCalendar->next();

                $startTime = $timestamp->getStartTime() ? $timestamp->getStartTime() : '00:00:00';
                $startDateString = $timestamp->getDate() . 'T' . $startTime;

                if ($timestamp->getEndTime()) {
                    $endDateString = $timestamp->getDate() . 'T' . $timestamp->getEndTime();
                } else {
                    $endDateString = $timestamp->getDate() . 'T' . $startTime;
                }

                $timestamps[] = $this->createTimestamp(
                    $startDateString,
                    $endDateString
                );
            }
        }

        //
        // Get the opening hours.
        //
        $cdbCalendar->rewind();
        $openingHours = [];

        $weekSchema = null;
        if ($cdbCalendar instanceof \CultureFeed_Cdb_Data_Calendar_PeriodList) {
            $period = $cdbCalendar->current();
            $weekSchema = $period->getWeekScheme();
        } else if ($cdbCalendar instanceof  \CultureFeed_Cdb_Data_Calendar_Permanent) {
            $weekSchema = $cdbCalendar->getWeekScheme();
        }

        if ($weekSchema) {
            $openingHours = $this->createOpeningHoursFromWeekScheme($weekSchema);
        }

        // End date might be before start date in cdbxml when event takes place
        // between e.g. 9 PM and 3 AM (the next day). UDB3 does not support this
        // and gracefully ignores the end time.
        //
        // Example cdbxml:
        //
        // <timestamp>
        //   <date>2016-12-16</date>
        //   <timestart>21:00:00</timestart>
        //   <timeend>05:00:00</timeend>
        // </timestamp>
        //
        if ($endDate < $startDate) {
            $endDate = $startDate;
        }

        //
        // Create the calendar value object.
        //
        return new Calendar(
            CalendarType::fromNative($calendarType),
            $startDate,
            $endDate,
            $timestamps,
            $openingHours
        );
    }

    /**
     * @param \CultureFeed_Cdb_Data_Calendar_Weekscheme|null $weekScheme
     * @return Calendar
     */
    public function createFromWeekScheme(
        \CultureFeed_Cdb_Data_Calendar_Weekscheme $weekScheme = null
    ) {
        $openingHours = [];

        if ($weekScheme) {
            $openingHours = $this->createOpeningHoursFromWeekScheme($weekScheme);
        }

        return new Calendar(
            CalendarType::PERMANENT(),
            null,
            null,
            [],
            $openingHours
        );
    }

    /**
     * @param \CultureFeed_Cdb_Data_Calendar_Weekscheme $weekScheme
     * @return OpeningHour[]
     */
    private function createOpeningHoursFromWeekScheme(
        \CultureFeed_Cdb_Data_Calendar_Weekscheme $weekScheme
    ) {
        $openingHours = [];

        foreach ($weekScheme->getDays() as $day) {
            if ($day->isOpen()) {
                /** @var \CultureFeed_Cdb_Data_Calendar_OpeningTime[] $openingTimes */
                $openingTimes = $day->getOpeningTimes();

                // A day could be marked as open but without any hours.
                // This means all day open but needs to be mapped to 00:00:00.
                if (count($openingTimes) === 0) {
                    $openingTimes[] = new \CultureFeed_Cdb_Data_Calendar_OpeningTime(
                        '00:00:00',
                        '00:00:00'
                    );
                }

                foreach ($openingTimes as $openingTime) {
                    $opens = \DateTime::createFromFormat(
                        'H:i:s',
                        $openingTime->getOpenFrom()
                    );
                    $closes = \DateTime::createFromFormat(
                        'H:i:s',
                        $openingTime->getOpenTill()
                    );

                    $openingHour = new OpeningHour(
                        OpeningTime::fromNativeDateTime($opens),
                        $closes ? OpeningTime::fromNativeDateTime($closes) : OpeningTime::fromNativeDateTime($opens),
                        DayOfWeek::fromNative($day->getDayName())
                    );

                    $openingHours = $this->addToOpeningHours($openingHour, ...$openingHours);
                }
            }
        }

        return $openingHours;
    }

    /**
     * @param OpeningHour $newOpeningHour
     * @param OpeningHour[] ...$openingHours
     * @return OpeningHour[]
     */
    private function addToOpeningHours(
        OpeningHour $newOpeningHour,
        OpeningHour ...$openingHours
    ) {
        foreach ($openingHours as $openingHour) {
            if ($openingHour->hasEqualHours($newOpeningHour)) {
                $openingHour->addDaysOfWeek(...$newOpeningHour->getDaysOfWeek());
                return $openingHours;
            }
        }

        $openingHours[] = $newOpeningHour;
        return $openingHours;
    }

    /**
     * @param string $startDateString
     * @param string $endDateString
     * @return Timestamp
     */
    private function createTimestamp(
        $startDateString,
        $endDateString
    ) {
        $startDate = DateTimeFactory::dateTimeFromDateString($startDateString);
        $endDate = DateTimeFactory::dateTimeFromDateString($endDateString);

        // End date might be before start date in cdbxml when event takes place
        // between e.g. 9 PM and 3 AM (the next day). UDB3 does not support this
        // and gracefully ignores the end time.
        //
        // Example cdbxml:
        //
        // <timestamp>
        //   <date>2016-12-16</date>
        //   <timestart>21:00:00</timestart>
        //   <timeend>05:00:00</timeend>
        // </timestamp>
        //
        if ($endDate < $startDate) {
            $endDate = $startDate;
        }

        return new Timestamp($startDate, $endDate);
    }
}
