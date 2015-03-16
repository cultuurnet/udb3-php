<?php

/**
 * @file
 * Contains CultuurNet\UDB3\UDB2\Udb2UtilityTrait.
 */

namespace CultuurNet\UDB3\UDB2;

use Broadway\Domain\Metadata;
use CultureFeed_Cdb_Data_Address_PhysicalAddress;
use CultureFeed_Cdb_Data_Calendar_BookingPeriod;
use CultureFeed_Cdb_Data_Calendar_OpeningTime;
use CultureFeed_Cdb_Data_Calendar_Period;
use CultureFeed_Cdb_Data_Calendar_PeriodList;
use CultureFeed_Cdb_Data_Calendar_Permanent;
use CultureFeed_Cdb_Data_Calendar_SchemeDay;
use CultureFeed_Cdb_Data_Calendar_Timestamp;
use CultureFeed_Cdb_Data_Calendar_TimestampList;
use CultureFeed_Cdb_Data_Calendar_Weekscheme;
use CultureFeed_Cdb_Data_ContactInfo;
use CultureFeed_Cdb_Data_EventDetail;
use CultureFeed_Cdb_Data_File;
use CultureFeed_Cdb_Data_Mail;
use CultureFeed_Cdb_Data_Phone;
use CultureFeed_Cdb_Data_Url;
use CultureFeed_Cdb_Item_Base;
use CultureFeed_Cdb_Item_Event;
use CultuurNet\Entry\EntryAPI;
use CultuurNet\UDB3\Address;
use CultuurNet\UDB3\BookingInfo;
use CultuurNet\UDB3\Calendar;
use CultuurNet\UDB3\ContactPoint;
use CultuurNet\UDB3\Event\Events\EventCreated;
use CultuurNet\UDB3\MediaObject;
use CultuurNet\UDB3\Place\Events\PlaceCreated;
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

        // Store opening hours.
        $openingHours = $eventCalendar->getOpeningHours();
        $weekScheme = null;

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

        }

        // Multiple days.
        if ($eventCalendar->getType() == Calendar::MULTIPLE) {
            $calendar = new CultureFeed_Cdb_Data_Calendar_TimestampList();
            foreach ($eventCalendar->getTimestamps() as $timestamp) {
                $startdate = strtotime($timestamp->getStartDate());
                $enddate = strtotime($timestamp->getEndDate());
                $startHour = date('H:i:s', $startdate);
                if ($startHour == '00:00:00') {
                    $startHour = null;
                }
                $endHour = date('H:i:s', $enddate);
                if ($endHour == '00:00:00') {
                    $endHour = null;
                }
                $calendar->add(
                    new CultureFeed_Cdb_Data_Calendar_Timestamp(
                        date('Y-m-d', $startdate),
                        $startHour,
                        $endHour
                    )
                );
            }

        // Single day
        } elseif ($eventCalendar->getType() == Calendar::SINGLE) {
            $calendar = new CultureFeed_Cdb_Data_Calendar_TimestampList();
            $startdate = strtotime($eventCalendar->getStartDate());
            $enddate = strtotime($eventCalendar->getEndDate());
            $startHour = date('H:i:s', $startdate);
            if ($startHour == '00:00:00') {
                $startHour = null;
            }
            $endHour = date('H:i:s', $enddate);
            if ($endHour == '00:00:00') {
                $endHour = null;
            }
            $calendar->add(
                new CultureFeed_Cdb_Data_Calendar_Timestamp(
                    date('Y-m-d', $startdate),
                    $startHour,
                    $endHour
                )
            );
        // Period.
        } elseif ($eventCalendar->getType() == Calendar::PERIODIC) {
            $calendar = new CultureFeed_Cdb_Data_Calendar_PeriodList();
            $startdate = date('Y-m-d', strtotime($eventCalendar->getStartDate()));
            $enddate = date('Y-m-d', strtotime($eventCalendar->getEndDate()));

            $period = new CultureFeed_Cdb_Data_Calendar_Period($startdate, $enddate);
            if (!empty($weekScheme)) {
                $calendar->setWeekScheme($weekscheme);
            }
            $calendar->add($period);

        // Permanent
        } elseif ($eventCalendar->getType() == Calendar::PERMANENT) {
            $calendar = new CultureFeed_Cdb_Data_Calendar_Permanent();
            if (!empty($weekScheme)) {
                $calendar->setWeekScheme($weekscheme);
            }

        }

        $cdbEvent->setCalendar($calendar);

    }

    /**
     * Create a physical addres based on a given udb3 address.
     * @param Address $address
     */
    protected function getPhysicalAddressForUdb3Address(Address $address)
    {

        $physicalAddress = new CultureFeed_Cdb_Data_Address_PhysicalAddress();
        $physicalAddress->setCountry($address->getCountry());
        $physicalAddress->setCity($address->getLocality());
        $physicalAddress->setZip($address->getPostalCode());

        // @todo This is not an exact mapping, because we do not have a separate
        // house number in JSONLD, this should be fixed somehow. Probably it's
        // better to use another read model than JSON-LD for this purpose.
        $streetParts = explode(' ', $address->getStreetAddress());

        if (count($streetParts) > 1) {
            $number = array_pop($streetParts);
            $physicalAddress->setStreet(implode(' ', $streetParts));
            $physicalAddress->setHouseNumber($number);
        } else {
            $physicalAddress->setStreet($address->getStreetAddress());
        }

        return $physicalAddress;

    }

    /**
     * Update a cdb item based on a contact point.
     * @param CultureFeed_Cdb_Item_Base $cdbItem
     * @param \CultuurNet\UDB3\UDB2\ContactPoint $contactPoint
     */
    private function updateCdbItemByContactPoint(
        CultureFeed_Cdb_Item_Base $cdbItem,
        ContactPoint $contactPoint
    ) {

        $contactInfo = $cdbItem->getContactInfo();

        // Remove non-reservation phones and add new ones.
        foreach ($contactInfo->getPhones() as $phoneIndex => $phone) {
            if (!$phone->isForReservations()) {
                $contactInfo->removePhone($phoneIndex);
            }
        }
        $phones = $contactPoint->getPhones();
        foreach ($phones as $phone) {
            $contactInfo->addPhone(new CultureFeed_Cdb_Data_Phone($phone));
        }

        // Remove non-reservation urls and add new ones.
        foreach ($contactInfo->getUrls() as $urlIndex => $url) {
            if (!$url->isForReservations()) {
                $contactInfo->removeUrl($urlIndex);
            }
        }
        $urls = $contactPoint->getUrls();
        foreach ($urls as $url) {
            $contactInfo->addUrl(new CultureFeed_Cdb_Data_Url($url));
        }

        // Remove non-reservation emails and add new ones.
        foreach ($contactInfo->getMails() as $mailIndex => $mail) {
            if (!$mail->isForReservations()) {
                $contactInfo->removeUrl($mailIndex);
            }
        }
        $emails = $contactPoint->getEmails();
        foreach ($emails as $email) {
            $contactInfo->addMail(new CultureFeed_Cdb_Data_Mail($email));
        }
        $cdbItem->setContactInfo($contactInfo);

    }

    /**
     * Update the cdb item based on a bookingInfo object.
     */
    private function updateCdbItemByBookingInfo(
        CultureFeed_Cdb_Item_Base $cdbItem,
        BookingInfo $bookingInfo
    ) {

        // Add the booking Period.
        $bookingPeriod = $cdbItem->getBookingPeriod();
        if (empty($bookingPeriod)) {
            $bookingPeriod = new CultureFeed_Cdb_Data_Calendar_BookingPeriod();
        }

        if (!empty($bookingInfo->availabilityStarts)) {
            $bookingPeriod->setDateFrom($bookingInfo->availabilityStarts);
        }
        if (!empty($bookingInfo->availabilityEnds)) {
            $bookingPeriod->setDateTill($bookingInfo->availabilityEnds);
        }
        $cdbItem->setBookingPeriod($bookingPeriod);

        // Add the contact info.
        $contactInfo = $cdbItem->getContactInfo();
        if (empty($contactInfo)) {
            $contactInfo = new CultureFeed_Cdb_Data_ContactInfo();
        }
        if (!empty($bookingInfo->phone)) {
            foreach ($contactInfo->getPhones() as $phoneIndex => $phone) {
                if ($phone->isForReservations()) {
                    $contactInfo->removePhone($phoneIndex);
                }
            }
            $contactInfo->addPhone(new CultureFeed_Cdb_Data_Phone($bookingInfo->phone));
        }

        if (!empty($bookingInfo->url)) {
            foreach ($contactInfo->getUrls() as $urlIndex => $url) {
                if ($url->isForReservations()) {
                    $contactInfo->removeUrl($urlIndex);
                }
            }
            $contactInfo->addUrl(new CultureFeed_Cdb_Data_Url($bookingInfo->url));
        }

        if (!empty($bookingInfo->email)) {
            foreach ($contactInfo->getMails() as $mailIndex => $mail) {
                if ($mail->isForReservations()) {
                    $contactInfo->removeUrl($mailIndex);
                }
            }
            $contactInfo->addMail(new CultureFeed_Cdb_Data_Mail($bookingInfo->email));
        }
        $cdbItem->setContactInfo($contactInfo);

    }

    /**
     * Add an image to the cdb item.
     */
    private function addImageToCdbItem(
        CultureFeed_Cdb_Item_Base $cdbItem,
        MediaObject $mediaObject
    ) {

        $details = $cdbItem->getDetails();

        // Get the first detail.
        $detail = null;
        foreach ($details as $languageDetail) {
            if (!$detail) {
                $detail = $languageDetail;
            }
        }

        // Make sure a detail exists.
        if (empty($detail)) {
          $detail = new CultureFeed_Cdb_Data_EventDetail();
          $details->add($detail);
        }

        $uriParts = explode('/', $mediaObject->getUrl());

        $file = new CultureFeed_Cdb_Data_File();
        $file->setMediaType(CultureFeed_Cdb_Data_File::MEDIA_TYPE_PHOTO);
        $file->setHLink($mediaObject->getUrl());
        $file->setFileName(end($uriParts));
        $file->setCopyright($mediaObject->getCopyrightHolder());
        $file->setTitle($mediaObject->getDescription());
        $detail->getMedia()->add($file);

    }

    /**
     * Update an existing image on the cdb item.
     *
     * @param CultureFeed_Cdb_Item_Base $cdbItem
     * @param int $indexToUpdate
     * @param MediaObject $mediaObject
     */
    private function updateImageOnCdbItem(
        CultureFeed_Cdb_Item_Base $cdbItem,
        $indexToUpdate,
        MediaObject $mediaObject
    ) {

        $details = $cdbItem->getDetails();

        // Get the first detail.
        $detail = null;
        foreach ($details as $languageDetail) {
            if (!$detail) {
                $detail = $languageDetail;
            }
        }

        // Make sure a detail exists.
        if (empty($detail)) {
          $detail = new CultureFeed_Cdb_Data_EventDetail();
          $details->add($detail);
        }

        $media = $detail->getMedia();
        $index = 0;
        // Loop over all files and count own index.
        foreach ($media as $file) {
            if ($file->getMediatype === CultureFeed_Cdb_Data_File::MEDIA_TYPE_PHOTO) {
                // If the index matches, delete the file.
                if ($index === $indexToUpdate) {

                    $uriParts = explode('/', $mediaObject->getUrl());

                    $file->setHLink($mediaObject->getUrl());
                    $file->setFileName(end($uriParts));
                    $file->setCopyright($mediaObject->getCopyrightHolder());
                    $file->setTitle($mediaObject->getDescription());

                    break;
                }
                $index++;
            }
        }

    }

    /**
     * Delete a given index on the cdb item.
     *
     * @param CultureFeed_Cdb_Item_Base $cdbItem
     * @param int $indexToDelete
     * @param MediaObject $mediaObject
     */
    private function deleteImageOnCdbItem(
        CultureFeed_Cdb_Item_Base $cdbItem,
        $indexToDelete
    ) {

        $details = $cdbItem->getDetails();

        // Get the first detail.
        $detail = null;
        foreach ($details as $languageDetail) {
            if (!$detail) {
                $detail = $languageDetail;
            }
        }

        // No detail = nothing to delete.
        if (empty($detail)) {
          return;
        }

        $media = $detail->getMedia();
        $index = 0;
        // Loop over all files and count own index.
        foreach ($media as $key => $file) {
            if ($file->getMediatype === CultureFeed_Cdb_Data_File::MEDIA_TYPE_PHOTO) {
                // If the index matches, delete the file.
                if ($index === $indexToDelete) {
                    $media->remove($key);
                    break;
                }
                $index++;
            }
        }

    }
}
