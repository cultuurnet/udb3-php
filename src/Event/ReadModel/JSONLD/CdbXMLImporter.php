<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Event\ReadModel\JSONLD;

/**
 * Takes care of importing cultural events in the CdbXML format (UDB2)
 * into a UDB3 JSON-LD document.
 */
class CdbXMLImporter
{
    /**
     * Imports a UDB2 event into a UDB3 JSON-LD document.
     *
     * @param \stdClass $base
     *   The JSON-LD document to start from.
     * @param \CultureFeed_Cdb_Item_Event $event
     *   The cultural event data from UDB2 to import.
     *
     * @return \stdClass
     *   The document with the UDB2 event data merged in.
     */
    public function documentWithCdbXML(
        $base,
        \CultureFeed_Cdb_Item_Event $event,
        PlaceServiceInterface $placeManager,
        OrganizerServiceInterface $organizerManager
    ) {
        $jsonLD = clone $base;

        /** @var \CultureFeed_Cdb_Data_EventDetail $detail */
        $detail = null;

        /** @var \CultureFeed_Cdb_Data_EventDetail[] $details */
        $details = $event->getDetails();

        foreach ($details as $languageDetail) {
            $language = $languageDetail->getLanguage();

            // The first language detail found will be used to retrieve
            // properties from which in UDB3 are not any longer considered
            // to be language specific.
            if (!$detail) {
                $detail = $languageDetail;
            }

            $jsonLD->name[$language] = $languageDetail->getTitle();

            $this->importDescription($languageDetail, $jsonLD, $language);
        }

        $this->importPicture($detail, $jsonLD);

        $this->importKeywords($event, $jsonLD);

        $jsonLD->calendarSummary = $detail->getCalendarSummary();

        $this->importLocation($event, $placeManager, $jsonLD);

        $this->importOrganizer($event, $organizerManager, $jsonLD);

        $this->importBookingInfo($detail, $jsonLD);

        $this->importTerms($event, $jsonLD);

        $this->importPublicationInfo($event, $jsonLD);

        $this->importCalendar($event, $jsonLD);

        $this->importTypicalAgeRange($event, $jsonLD);

        $this->importPerformers($detail, $jsonLD);

        $this->importLanguages($event, $jsonLD);

        return $jsonLD;
    }

    /**
     * @param $dateString
     * @return \DateTime
     */
    private function dateFromUdb2DateString($dateString)
    {
        return \DateTime::createFromFormat(
            'Y-m-d?H:i:s',
            $dateString,
            new \DateTimeZone('Europe/Brussels')
        );
    }

    /**
     * @param \CultureFeed_Cdb_Data_EventDetail $languageDetail
     * @param \stdClass $jsonLD
     * @param string $language
     */
    private function importDescription($languageDetail, $jsonLD, $language)
    {
        $descriptions = [
            $languageDetail->getShortDescription(),
            $languageDetail->getLongDescription()
        ];
        $descriptions = array_filter($descriptions);
        $jsonLD->description[$language] = implode('<br/>', $descriptions);
    }

    /**
     * @param \CultureFeed_Cdb_Item_Event $event
     * @param $jsonLD
     */
    private function importKeywords(\CultureFeed_Cdb_Item_Event $event, $jsonLD)
    {
        $keywords = array_filter(
            array_values($event->getKeywords()),
            function ($keyword) {
                return (strlen(trim($keyword)) > 0);
            }
        );
        // Ensure keys are continuous after the filtering was applied, otherwise
        // JSON-encoding the array will result in an object.
        $keywords = array_values($keywords);

        if ($keywords) {
            $jsonLD->keywords = $keywords;
        }
    }

    /**
     * @param \CultureFeed_Cdb_Data_EventDetail $detail
     * @param \stdClass $jsonLD
     */
    private function importPicture($detail, $jsonLD)
    {
        $pictures = $detail->getMedia()->byMediaType(
            \CultureFeed_Cdb_Data_File::MEDIA_TYPE_PHOTO
        );

        $pictures->rewind();
        $picture = count($pictures) > 0 ? $pictures->current() : null;
        if ($picture) {
            $jsonLD->image = $picture->getHLink();
        }
    }

    /**
     * @param \CultureFeed_Cdb_Item_Event $event
     * @param PlaceServiceInterface $placeManager
     * @param \stdClass $jsonLD
     */
    private function importLocation(\CultureFeed_Cdb_Item_Event $event, PlaceServiceInterface $placeManager, $jsonLD)
    {
        $location = array();
        $location['@type'] = 'Place';

        $location_cdb = $event->getLocation();
        $location_id = $location_cdb->getCdbid();

        if ($location_id) {
            $location += (array)$placeManager->placeJSONLD($location_id);
        } else {
            $location['name'] = $location_cdb->getLabel();
            $address = $location_cdb->getAddress()->getPhysicalAddress();
            if ($address) {
                $location['address'] = array(
                    'addressCountry' => $address->getCountry(),
                    'addressLocality' => $address->getCity(),
                    'postalCode' => $address->getZip(),
                    'streetAddress' =>
                        $address->getStreet() . ' ' . $address->getHouseNumber(
                        ),
                );
            }
        }
        $jsonLD->location = $location;
    }

    /**
     * @param \CultureFeed_Cdb_Item_Event $event
     * @param OrganizerServiceInterface $organizerManager
     * @param \stdClass $jsonLD
     */
    private function importOrganizer(
        \CultureFeed_Cdb_Item_Event $event,
        OrganizerServiceInterface $organizerManager,
        $jsonLD
    ) {
// Organizer.
        $organizer_cdb = $event->getOrganiser();
        $contact_info_cdb = $event->getContactInfo();

        if ($organizer_cdb && $contact_info_cdb) {
            $organizer_id = $organizer_cdb->getCdbid();
            if ($organizer_id) {
                $organizer = $organizerManager->organizerJSONLD($organizer_id);
            } else {
                $organizer = array();
                $organizer['name'] = $organizer_cdb->getLabel();
                
                $emails_cdb = $contact_info_cdb->getMails();
                if (count($emails_cdb) > 0) {
                    $organizer['email'] = array();
                    foreach ($emails_cdb as $email) {
                        $organizer['email'][] = $email->getMailAddress();
                    }
                }

                $phones_cdb = $contact_info_cdb->getPhones();
                if (count($phones_cdb) > 0) {
                    $organizer['phone'] = array();
                    foreach ($phones_cdb as $phone) {
                        $organizer['phone'][] = $phone->getNumber();
                    }
                }
            }
            $jsonLD->organizer = $organizer;
        }
    }

    /**
     * @param \CultureFeed_Cdb_Data_EventDetail $detail
     * @param \stdClass $jsonLD
     */
    private function importBookingInfo(\CultureFeed_Cdb_Data_EventDetail $detail, $jsonLD)
    {
        $price = $detail->getPrice();

        if ($price) {
            $jsonLD->bookingInfo = array();
            // Booking info.
            $bookingInfo = array();
            if ($price->getDescription()) {
                $bookingInfo['description'] = $price->getDescription();
            }
            if ($price->getTitle()) {
                $bookingInfo['name'] = $price->getTitle();
            }
            if ($price->getValue() !== null) {
                $bookingInfo['currency'] = 'EUR';
                $bookingInfo['price'] = floatval($price->getValue());
            }
            $jsonLD->bookingInfo[] = $bookingInfo;
        }
    }

    /**
     * @param \CultureFeed_Cdb_Item_Event $event
     * @param \stdClass $jsonLD
     */
    private function importTerms(\CultureFeed_Cdb_Item_Event $event, $jsonLD)
    {
        $themeBlacklist = [
            'Thema onbepaald',
            'Meerder kunstvormen',
            'Meerdere filmgenres'
        ];
        $categories = array();
        foreach ($event->getCategories() as $category) {
            /* @var \Culturefeed_Cdb_Data_Category $category */
            if ($category && !in_array($category->getName(), $themeBlacklist)) {
                $categories[] = array(
                    'label' => $category->getName(),
                    'domain' => $category->getType(),
                    'id' => $category->getId(),
                );
            }
        }
        $jsonLD->terms = $categories;
    }

    /**
     * @param \CultureFeed_Cdb_Item_Event $event
     * @param \stdClass $jsonLD
     */
    private function importPublicationInfo(\CultureFeed_Cdb_Item_Event $event, $jsonLD)
    {
// Input info.
        $jsonLD->creator = $event->getCreatedBy();

        // format using ISO-8601 with time zone designator
        $creationDate = $this->dateFromUdb2DateString(
            $event->getCreationDate()
        );
        $jsonLD->created = $creationDate->format('c');

        $jsonLD->publisher = $event->getOwner();
    }

    /**
     * @param \CultureFeed_Cdb_Item_Event $event
     * @param \stdClass $jsonLD
     */
    private function importCalendar(\CultureFeed_Cdb_Item_Event $event, $jsonLD)
    {
        // To render the front-end we make a distinction between 4 calendar types
        // Permanent and Periodic map directly to the Cdb calendar classes
        // Simple timestamps are divided into single and multiple
        $calendarType = 'unknown';
        $calendar = $event->getCalendar();

        if ($calendar instanceof \CultureFeed_Cdb_Data_Calendar_Permanent) {
            $calendarType = 'permanent';
        } elseif ($calendar instanceof \CultureFeed_Cdb_Data_Calendar_PeriodList) {
            $calendarType = 'periodic';
            $calendar->rewind();
            $firstCalendarItem = $calendar->current();
            $startDateString = $firstCalendarItem->getDateFrom() . 'T00:00:00';
            $startDate = $this->dateFromUdb2DateString($startDateString);

            if (iterator_count($calendar) > 1) {
                $periodArray = iterator_to_array($calendar);
                $lastCalendarItem = end($periodArray);
            } else {
                $lastCalendarItem = $firstCalendarItem;
            }

            $endDateString = $lastCalendarItem->getDateTo() . 'T00:00:00';
            $endDate = $this->dateFromUdb2DateString($endDateString);

            $jsonLD->startDate = $startDate->format('c');
            $jsonLD->endDate = $endDate->format('c');
        } elseif ($calendar instanceof \CultureFeed_Cdb_Data_Calendar_TimestampList) {
            $calendarType = 'single';
            $calendar->rewind();
            /** @var \CultureFeed_Cdb_Data_Calendar_Timestamp $firstCalendarItem */
            $firstCalendarItem = $calendar->current();
            if ($firstCalendarItem->getStartTime()) {
                $dateString =
                    $firstCalendarItem->getDate(
                    ) . 'T' . $firstCalendarItem->getStartTime();
            } else {
                $dateString = $firstCalendarItem->getDate() . 'T00:00:00';
            }

            $startDate = $this->dateFromUdb2DateString($dateString);

            if (iterator_count($calendar) > 1) {
                $periodArray = iterator_to_array($calendar);
                $lastCalendarItem = end($periodArray);
            } else {
                $lastCalendarItem = $firstCalendarItem;
            }

            $endDateString = null;
            if ($lastCalendarItem->getEndTime()) {
                $endDateString =
                    $lastCalendarItem->getDate(
                    ) . 'T' . $lastCalendarItem->getEndTime();
            } else {
                if (iterator_count($calendar) > 1) {
                    $endDateString = $lastCalendarItem->getDate() . 'T00:00:00';
                }
            }

            if ($endDateString) {
                $endDate = $this->dateFromUdb2DateString($endDateString);
                $jsonLD->endDate = $endDate->format('c');

                if ($startDate->format('Ymd') != $endDate->format('Ymd')) {
                    $calendarType = 'multiple';
                }
            }

            $jsonLD->startDate = $startDate->format('c');
        }

        $jsonLD->calendarType = $calendarType;
    }

    /**
     * @param \CultureFeed_Cdb_Item_Event $event
     * @param \stdClass $jsonLD
     */
    private function importTypicalAgeRange(\CultureFeed_Cdb_Item_Event $event, $jsonLD)
    {
        $ageFrom = $event->getAgeFrom();
        if ($ageFrom) {
            $jsonLD->typicalAgeRange = "{$ageFrom}-";
        }
    }

    /**
     * @param \CultureFeed_Cdb_Data_EventDetail $detail
     * @param \stdClass $jsonLD
     */
    private function importPerformers(\CultureFeed_Cdb_Data_EventDetail $detail, $jsonLD)
    {
        /** @var \CultureFeed_Cdb_Data_Performer $performer */
        $performers = $detail->getPerformers();
        if ($performers) {
            foreach ($performers as $performer) {
                if ($performer->getLabel()) {
                    $performerData = new \stdClass();
                    $performerData->name = $performer->getLabel();
                    $jsonLD->performer[] = $performerData;
                }
            }
        }
    }

    /**
     * @param \CultureFeed_Cdb_Item_Event $event
     * @param \stdClass $jsonLD
     */
    private function importLanguages(\CultureFeed_Cdb_Item_Event $event, $jsonLD)
    {
        /** @var \CultureFeed_Cdb_Data_Language $udb2Language */
        $languages = $event->getLanguages();
        if ($languages) {
            $jsonLD->language = [];
            foreach ($languages as $udb2Language) {
                $jsonLD->language[] = $udb2Language->getLanguage();
            }
            $jsonLD->language = array_unique($jsonLD->language);
        }
    }
}
