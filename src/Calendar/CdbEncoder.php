<?php

namespace CultuurNet\UDB3\Calendar;

use CultureFeed_Cdb_Data_Calendar_OpeningTime;
use CultureFeed_Cdb_Data_Calendar_Period;
use CultureFeed_Cdb_Data_Calendar_PeriodList;
use CultureFeed_Cdb_Data_Calendar_Permanent;
use CultureFeed_Cdb_Data_Calendar_SchemeDay;
use CultureFeed_Cdb_Data_Calendar_Timestamp;
use CultureFeed_Cdb_Data_Calendar_TimestampList;
use CultureFeed_Cdb_Data_Calendar_Weekscheme;
use CultuurNet\UDB3\Calendar;
use CultuurNet\UDB3\CalendarInterface;
use CultuurNet\UDB3\CalendarType;
use DateTimeInterface;

/**
 * Class CdbEncoder
 *
 * Encodes an UDB3 calendar or serialized calendar data as a cdb calendar.
 * This encoder is set up to be compatible with the Symfony serializer component.
 *
 * @package CultuurNet\UDB3\Calendar
 */
class CdbEncoder
{
    /**
     * @param CalendarInterface|array $data
     * @param $format
     * @param array $context
     *
     * @return \CultureFeed_Cdb_Data_Calendar
     */
    public function encode($data, $format, array $context = array())
    {
        $calendar = $data instanceof CalendarInterface ? $data : Calendar::deserialize($data);

        $weekscheme = $this->getWeekscheme($calendar);

        switch ($calendar->getType()->toNative()) {
            case CalendarType::MULTIPLE:
                $cdbCalendar = new CultureFeed_Cdb_Data_Calendar_TimestampList();
                foreach ($calendar->getTimestamps() as $timestamp) {
                    $this->timestampCalendar(
                        $timestamp->getStartDate(),
                        $timestamp->getEndDate(),
                        $cdbCalendar
                    );
                }
                break;
            case CalendarType::SINGLE:
                $cdbCalendar = $this->timestampCalendar(
                    $calendar->getStartDate(),
                    $calendar->getEndDate(),
                    new CultureFeed_Cdb_Data_Calendar_TimestampList()
                );
                break;
            case CalendarType::PERIODIC:
                $cdbCalendar = new CultureFeed_Cdb_Data_Calendar_PeriodList();
                $startDate = $calendar->getStartDate()->format('Y-m-d');
                $endDate = $calendar->getEndDate()->format('Y-m-d');

                $period = new CultureFeed_Cdb_Data_Calendar_Period($startDate, $endDate);
                if (!empty($weekscheme) && !empty($weekscheme->getDays())) {
                    $period->setWeekScheme($weekscheme);
                }
                $cdbCalendar->add($period);
                break;
            case CalendarType::PERMANENT:
                $cdbCalendar = new CultureFeed_Cdb_Data_Calendar_Permanent();
                if (!empty($weekscheme)) {
                    $cdbCalendar->setWeekScheme($weekscheme);
                }
                break;
            default:
                $cdbCalendar = new CultureFeed_Cdb_Data_Calendar_Permanent();
        }

        return $cdbCalendar;
    }

    public function supportsEncoding($format)
    {
        return 'cbd' === $format;
    }

    /**
     * @param \CultuurNet\UDB3\CalendarInterface $itemCalendar
     * @return CultureFeed_Cdb_Data_Calendar_Weekscheme|null
     * @throws \Exception
     */
    private function getWeekscheme(CalendarInterface $itemCalendar)
    {
        // Store opening hours.
        $openingHours = $itemCalendar->getOpeningHours();
        $weekscheme = null;

        if (!empty($openingHours)) {
            $weekscheme = new CultureFeed_Cdb_Data_Calendar_Weekscheme();

            // Multiple opening times can happen on same day. Store them in array.
            $openingTimesPerDay = array(
                'monday' => array(),
                'tuesday' => array(),
                'wednesday' => array(),
                'thursday' => array(),
                'friday' => array(),
                'saturday' => array(),
                'sunday' => array(),
            );

            foreach ($openingHours as $openingHour) {
                // In CDB2 every day needs to be a seperate entry.
                if (is_array($openingHour)) {
                    $openingHour = (object) $openingHour;
                }
                foreach ($openingHour->getDayOfWeekCollection()->getDaysOfWeek() as $day) {
                    $openingTimesPerDay[$day->toNative()][] = new CultureFeed_Cdb_Data_Calendar_OpeningTime(
                        $openingHour->getOpens()->toNativeString() . ':00',
                        $openingHour->getCloses()->toNativeString() . ':00'
                    );
                }
            }

            // Create the opening times correctly
            foreach ($openingTimesPerDay as $day => $openingTimes) {
                // Empty == closed.
                if (empty($openingTimes)) {
                    $openingInfo = new CultureFeed_Cdb_Data_Calendar_SchemeDay(
                        $day,
                        CultureFeed_Cdb_Data_Calendar_SchemeDay::SCHEMEDAY_OPEN_TYPE_CLOSED
                    );
                } else {
                    // Add all opening times.
                    $openingInfo = new CultureFeed_Cdb_Data_Calendar_SchemeDay(
                        $day,
                        CultureFeed_Cdb_Data_Calendar_SchemeDay::SCHEMEDAY_OPEN_TYPE_OPEN
                    );
                    foreach ($openingTimes as $openingTime) {
                        $openingInfo->addOpeningTime($openingTime);
                    }
                }
                $weekscheme->setDay($day, $openingInfo);
            }
        }

        return $weekscheme;
    }

    /**
     * @param DateTimeInterface $startDate
     * @param DateTimeInterface $endDate
     * @param CultureFeed_Cdb_Data_Calendar_TimestampList $calendar
     *
     * @return CultureFeed_Cdb_Data_Calendar_TimestampList
     */
    private function timestampCalendar(
        DateTimeInterface $startDate,
        DateTimeInterface $endDate,
        CultureFeed_Cdb_Data_Calendar_TimestampList $calendar
    ) {
        $startHour = $startDate->format('H:i:s');
        if ($startHour == '00:00:00') {
            $startHour = null;
        }
        $endHour = $endDate->format('H:i:s');
        if ($endHour == '00:00:00') {
            $endHour = null;
        }
        $calendar->add(
            new CultureFeed_Cdb_Data_Calendar_Timestamp(
                $startDate->format('Y-m-d'),
                $startHour,
                $endHour
            )
        );

        return $calendar;
    }
}