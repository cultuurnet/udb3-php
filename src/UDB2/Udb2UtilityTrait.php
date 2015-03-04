<?php

/**
 * @file
 * Contains CultuurNet\UDB3\UDB2\Udb2UtilityTrait.
 */

namespace CultuurNet\UDB3\UDB2;

use Broadway\Domain\Metadata;
use CultureFeed_Cdb_Data_Calendar_OpeningTime;
use CultureFeed_Cdb_Data_Calendar_Period;
use CultureFeed_Cdb_Data_Calendar_PeriodList;
use CultureFeed_Cdb_Data_Calendar_Permanent;
use CultureFeed_Cdb_Data_Calendar_SchemeDay;
use CultureFeed_Cdb_Data_Calendar_Timestamp;
use CultureFeed_Cdb_Data_Calendar_TimestampList;
use CultureFeed_Cdb_Data_Calendar_Weekscheme;
use CultureFeed_Cdb_Item_Event;
use CultuurNet\Entry\EntryAPI;
use CultuurNet\UDB3\Calendar;
use CultuurNet\UDB3\Event\EventCreated;
use CultuurNet\UDB3\Place\PlaceCreated;
use Zend\Validator\Exception\RuntimeException;

/**
 * Udb2Utility trait for sending data to UDB2.
 */
trait Udb2UtilityTrait
{

    /**
     * @param Metadata $metadata
     * @return EntryAPI
     */
    public function createImprovedEntryAPIFromMetadata(Metadata $metadata)
    {
        $metadata = $metadata->serialize();
        if (!isset($metadata['uitid_token_credentials'])) {
            throw new RuntimeException('No token credentials found. They are needed to access the entry API, so aborting request.');
        }
        $tokenCredentials = $metadata['uitid_token_credentials'];
        $entryAPI = $this->entryAPIImprovedFactory->withTokenCredentials(
            $tokenCredentials
        );

        return $entryAPI;
    }

    /**
     * Set the calendar on the cdb event based on an eventCreated event.
     *
     * @param EventCreated|PlaceCreated $createdEvent
     * @param CultureFeed_Cdb_Item_Event $cdbEvent
     */
    public function setCalendarForItemCreated($createdEvent, CultureFeed_Cdb_Item_Event $cdbEvent)
    {

        $eventCalendar = $createdEvent->getCalendar();
        if ($eventCalendar->getType() == Calendar::MULTIPLE) {
            $calendar = new CultureFeed_Cdb_Data_Calendar_TimestampList();
            foreach ($eventCalendar->getTimestamps() as $timestamp) {
                $startdate = strtotime($timestamp->getStartDate());
                $enddate = strtotime($timestamp->getEndDate());
                $calendar->add(
                    new CultureFeed_Cdb_Data_Calendar_Timestamp(
                        date('d-m-Y', $startdate),
                        date('H:i', $startdate),
                        date('H:i', $enddate)
                    )
                );
            }

        } elseif ($eventCalendar->getType() == Calendar::MULTIPLE) {
            $calendar = new CultureFeed_Cdb_Data_Calendar_TimestampList();
            $startdate = strtotime($eventCalendar->getStartDate());
            $enddate = strtotime($eventCalendar->getEndDate());
            $startHour = date('H:i', $startdate);
            if ($startHour == '00:00') {
                $startHour = null;
            }
            $endHour = date('H:i', $enddate);
            if ($endHour == '00:00') {
                $endHour = null;
            }
            $calendar->add(
                new CultureFeed_Cdb_Data_Calendar_Timestamp(
                    date('d-m-Y', $startdate),
                    $startHour,
                    $endHour
                )
            );
        } elseif ($eventCalendar->getType() == Calendar::PERIODIC) {
            $calendar = new CultureFeed_Cdb_Data_Calendar_PeriodList();
            $startdate = strtotime($eventCalendar->getStartDate());
            $enddate = strtotime($eventCalendar->getEndDate());
            $calendar->add(new CultureFeed_Cdb_Data_Calendar_Period($startdate, $enddate));
        } elseif ($eventCalendar->getType() == Calendar::PERMANENT) {
            $calendar = new CultureFeed_Cdb_Data_Calendar_Permanent();
        }

        // Store opening hours.
        $openingHours = $eventCalendar->getOpeningHours();
        if (!empty($openingHours)) {
            // CDB2 requires an entry for every day.
            $requiredDays = array(
                'monday',
                'tuesday',
                'wednesday',
                'thursday',
                'friday',
                'saturday',
                'sunday',
            );
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
                foreach ($openingHour->daysOfWeek as $day) {
                    $openingTimesPerDay[$day][] = new CultureFeed_Cdb_Data_Calendar_OpeningTime($openingHour->opens . ':00', $openingHour->closes . ':00');
                }

            }

            // Create the opening times correctly
            foreach ($openingTimesPerDay as $day => $openingTimes) {
                // Empty == closed.
                if (empty($openingTimes)) {
                    $openingInfo = new CultureFeed_Cdb_Data_Calendar_SchemeDay($day, CultureFeed_Cdb_Data_Calendar_SchemeDay::SCHEMEDAY_OPEN_TYPE_CLOSED);
                } else {
                    // Add all opening times.
                    $openingInfo = new CultureFeed_Cdb_Data_Calendar_SchemeDay($day, CultureFeed_Cdb_Data_Calendar_SchemeDay::SCHEMEDAY_OPEN_TYPE_OPEN);
                    foreach ($openingTimes as $openingTime) {
                        $openingInfo->addOpeningTime($openingTime);
                    }
                }

                $weekscheme->setDay($day, $openingInfo);
            }

            $calendar->setWeekScheme($weekscheme);
        }

        $cdbEvent->setCalendar($calendar);

    }
}
