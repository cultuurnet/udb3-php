<?php

namespace CultuurNet\UDB3;

use CultuurNet\UDB3\Cdb\DateTimeFactory;
use ValueObjects\DateTime\Time;
use ValueObjects\DateTime\WeekDay;

class CalendarFactory
{
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

                if ($timestamp->getStartTime()) {
                    $startDateString = $timestamp->getDate() . 'T' . $timestamp->getStartTime();

                    if ($timestamp->getEndTime()) {
                        $endDateString = $timestamp->getDate() . 'T' . $timestamp->getEndTime();
                    } else {
                        $endTime = $timestamp->getStartTime() ? $timestamp->getStartTime() : '00:00:00';
                        $endDateString = $timestamp->getDate() . 'T' . $endTime;
                    }
                }

                $timestamps[] = new Timestamp(
                    DateTimeFactory::dateTimeFromDateString($startDateString),
                    DateTimeFactory::dateTimeFromDateString($endDateString)
                );
            }
        }

        //
        // Get the opening hours.
        //
        $cdbCalendar->rewind();
        $openingHoursAsArray = [];

        $weekSchema = null;
        if ($cdbCalendar instanceof \CultureFeed_Cdb_Data_Calendar_PeriodList) {
            $period = $cdbCalendar->current();
            $weekSchema = $period->getWeekScheme();
        } else if ($cdbCalendar instanceof  \CultureFeed_Cdb_Data_Calendar_Permanent) {
            $weekSchema = $cdbCalendar->getWeekScheme();
        }

        if ($weekSchema) {
            $openingHours = $this->createOpeningHoursFromWeekScheme(
                $weekSchema
            );

            $openingHoursAsArray = $this->openingHoursToArray($openingHours);
        }

        //
        // Create the calendar value object.
        //
        return new Calendar(
            CalendarType::fromNative($calendarType),
            $startDate,
            $endDate,
            $timestamps,
            $openingHoursAsArray
        );
    }

    /**
     * @param \CultureFeed_Cdb_Data_Calendar_Weekscheme $weekscheme
     * @return Calendar
     */
    public function createFromWeekScheme(
        \CultureFeed_Cdb_Data_Calendar_Weekscheme $weekscheme
    ) {
        $openingHours = $this->createOpeningHoursFromWeekScheme($weekscheme);

        $openingHoursAsArray = $this->openingHoursToArray($openingHours);

        return new Calendar(
            CalendarType::PERMANENT(),
            null,
            null,
            null,
            $openingHoursAsArray
        );
    }

    /**
     * @param \CultureFeed_Cdb_Data_Calendar_Weekscheme $weekScheme
     * @return OpeningHour[]
     */
    private function createOpeningHoursFromWeekScheme(
        \CultureFeed_Cdb_Data_Calendar_Weekscheme $weekScheme
    ) {
        $days = $weekScheme->getDays();

        /** @var OpeningHour[] $openingHours */
        $openingHours = [];
        foreach ($days as $day) {
            if ($day->isOpen()) {
                /** @var \CultureFeed_Cdb_Data_Calendar_OpeningTime[] $openingTimes */
                $openingTimes = $day->getOpeningTimes();
                $opens = \DateTime::createFromFormat(
                    'H:i:s',
                    $openingTimes[0]->getOpenFrom()
                );
                $closes = \DateTime::createFromFormat(
                    'H:i:s',
                    $openingTimes[0]->getOpenTill()
                );

                $newOpeningHour = new OpeningHour(
                    WeekDay::fromNative(ucfirst($day->getDayName())),
                    Time::fromNativeDateTime($opens),
                    $closes ? Time::fromNativeDateTime($closes) : Time::fromNativeDateTime($opens)
                );

                $merged = false;
                foreach ($openingHours as $openingHour) {
                    if ($openingHour->equalHours($newOpeningHour)) {
                        $openingHour->mergeWeekday($newOpeningHour);
                        $merged = true;
                        break;
                    }
                }

                if (!$merged) {
                    $openingHours[] = $newOpeningHour;
                }
            }
        }

        return $openingHours;
    }

    /**
     * @param array $openingHours
     * @return array
     */
    private function openingHoursToArray(array $openingHours)
    {
        $openingHoursAsArray = [];

        if (count($openingHours) > 0) {
            foreach ($openingHours as $openingHour) {
                $openingHoursAsArray[] = [
                    'dayOfWeek' => array_map(
                        function (WeekDay $weekDay) {
                            return strtolower($weekDay->toNative());
                        },
                        $openingHour->getWeekDays()
                    ),
                    'opens' => $openingHour->getOpens()->toNativeDateTime()->format('H:i'),
                    'closes' => (string)$openingHour->getCloses()->toNativeDateTime()->format('H:i'),
                ];
            }
        }

        return $openingHoursAsArray;
    }
}
