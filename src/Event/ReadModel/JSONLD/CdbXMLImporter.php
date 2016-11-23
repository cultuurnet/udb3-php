<?php

namespace CultuurNet\UDB3\Event\ReadModel\JSONLD;

use CultureFeed_Cdb_Data_File;
use CultureFeed_Cdb_Data_Keyword;
use CultuurNet\UDB3\Cdb\CdbId\EventCdbIdExtractorInterface;
use CultuurNet\UDB3\Cdb\DateTimeFactory;
use CultuurNet\UDB3\Cdb\PriceDescriptionParser;
use CultuurNet\UDB3\LabelCollection;
use CultuurNet\UDB3\Offer\ReadModel\JSONLD\CdbXMLItemBaseImporter;
use CultuurNet\UDB3\SluggerInterface;
use CultuurNet\UDB3\StringFilter\StringFilterInterface;

/**
 * Takes care of importing cultural events in the CdbXML format (UDB2)
 * into a UDB3 JSON-LD document.
 */
class CdbXMLImporter
{
    /**
     * @var CdbXMLItemBaseImporter
     */
    private $cdbXMLItemBaseImporter;

    /**
     * @var EventCdbIdExtractorInterface
     */
    private $cdbIdExtractor;

    /**
     * @var PriceDescriptionParser
     */
    private $priceDescriptionParser;

    /**
     * @var StringFilterInterface[]
     */
    private $descriptionFilters = [];

    /**
     * @param CdbXMLItemBaseImporter $dbXMLItemBaseImporter
     * @param EventCdbIdExtractorInterface $cdbIdExtractor
     */
    public function __construct(
        CdbXMLItemBaseImporter $dbXMLItemBaseImporter,
        EventCdbIdExtractorInterface $cdbIdExtractor,
        PriceDescriptionParser $priceDescriptionParser
    ) {
        $this->cdbXMLItemBaseImporter = $dbXMLItemBaseImporter;
        $this->cdbIdExtractor = $cdbIdExtractor;
        $this->priceDescriptionParser = $priceDescriptionParser;
    }

    /**
     * Imports a UDB2 event into a UDB3 JSON-LD document.
     *
     * @param \stdClass $base
     *   The JSON-LD document to start from.
     * @param \CultureFeed_Cdb_Item_Event $event
     *   The cultural event data from UDB2 to import.
     * @param PlaceServiceInterface $placeManager
     *   The manager from which to retrieve the JSON-LD of a place.
     * @param OrganizerServiceInterface $organizerManager
     *   The manager from which to retrieve the JSON-LD of an organizer.
     * @param SluggerInterface $slugger
     *   The slugger that's used to generate a sameAs reference.
     *
     * @return \stdClass
     *   The document with the UDB2 event data merged in.
     */
    public function documentWithCdbXML(
        $base,
        \CultureFeed_Cdb_Item_Event $event,
        PlaceServiceInterface $placeManager,
        OrganizerServiceInterface $organizerManager,
        SluggerInterface $slugger
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

        $this->cdbXMLItemBaseImporter->importAvailable($event, $jsonLD);

        $this->importPicture($detail, $jsonLD);

        $this->importLabels($event, $jsonLD);

        $jsonLD->calendarSummary = $detail->getCalendarSummary();

        $this->importLocation($event, $placeManager, $jsonLD);

        $this->importOrganizer($event, $organizerManager, $jsonLD);

        $this->importBookingInfo($event, $detail, $jsonLD);

        $this->importPriceInfo($detail, $jsonLD);

        $this->importTerms($event, $jsonLD);

        $this->cdbXMLItemBaseImporter->importPublicationInfo($event, $jsonLD);

        $this->importCalendar($event, $jsonLD);

        $this->importTypicalAgeRange($event, $jsonLD);

        $this->importPerformers($detail, $jsonLD);

        $this->importLanguages($event, $jsonLD);

        $this->importUitInVlaanderenReference($event, $slugger, $jsonLD);

        $this->cdbXMLItemBaseImporter->importExternalId($event, $jsonLD);

        $this->importSeeAlso($event, $jsonLD);

        $this->importContactPoint($event, $jsonLD);

        $this->cdbXMLItemBaseImporter->importWorkflowStatus($event, $jsonLD);

        return $jsonLD;
    }

    /**
     * @param StringFilterInterface $filter
     */
    public function addDescriptionFilter(StringFilterInterface $filter)
    {
        $this->descriptionFilters[] = $filter;
    }

    /**
     * @param int $unixTime
     * @return \DateTime
     */
    private function dateFromUdb2UnixTime($unixTime)
    {
        $dateTime = new \DateTime(
            '@' . $unixTime,
            new \DateTimeZone('Europe/Brussels')
        );

        return $dateTime;
    }

    /**
     * @param \CultureFeed_Cdb_Data_EventDetail $languageDetail
     * @param \stdClass $jsonLD
     * @param string $language
     */
    private function importDescription($languageDetail, $jsonLD, $language)
    {
        $longDescription = trim($languageDetail->getLongDescription());
        $shortDescription = $languageDetail->getShortDescription();

        $descriptions = [];

        if ($shortDescription) {
            $shortDescription = trim($shortDescription);

            if (0 !== strncmp($longDescription, $shortDescription, mb_strlen($shortDescription))) {
                $descriptions[] = $shortDescription;
            }
        }

        foreach ($this->descriptionFilters as $descriptionFilter) {
            $longDescription = $descriptionFilter->filter($longDescription);
        };

        $descriptions[] = $longDescription;

        $descriptions = array_filter($descriptions);
        $description = implode("\n\n", $descriptions);

        $jsonLD->description[$language] = $description;
    }

    /**
     * @param \CultureFeed_Cdb_Item_Event $event
     * @param $jsonLD
     */
    private function importLabels(\CultureFeed_Cdb_Item_Event $event, $jsonLD)
    {
        /** @var CultureFeed_Cdb_Data_Keyword[] $keywords */
        $keywords = array_values($event->getKeywords(true));

        $validKeywords = array_filter(
            $keywords,
            function (CultureFeed_Cdb_Data_Keyword $keyword) {
                return strlen(trim($keyword->getValue())) > 0;
            }
        );

        $visibleKeywords = array_filter(
            $validKeywords,
            function (CultureFeed_Cdb_Data_Keyword $keyword) {
                return $keyword->isVisible();
            }
        );

        $hiddenKeywords = array_filter(
            $validKeywords,
            function (CultureFeed_Cdb_Data_Keyword $keyword) {
                return !$keyword->isVisible();
            }
        );

        $this->addKeywordsAsLabelsProperty($jsonLD, 'labels', $visibleKeywords);
        $this->addKeywordsAsLabelsProperty($jsonLD, 'hiddenLabels', $hiddenKeywords);
    }

    /**
     * @param object $jsonLD
     * @param string $labelsPropertyName
     *  The property where the labels should be listed. Used the differentiate between visible and hidden labels.
     * @param CultureFeed_Cdb_Data_Keyword[] $keywords
     */
    private function addKeywordsAsLabelsProperty($jsonLD, $labelsPropertyName, array $keywords)
    {
        $labels = array_map(
            function ($keyword) {
                /** @var CultureFeed_Cdb_Data_Keyword $keyword */
                return $keyword->getValue();
            },
            $keywords
        );

        // Create a label collection to get rid of duplicates.
        $labelCollection = LabelCollection::fromStrings($labels);

        if (count($labelCollection) > 0) {
            $jsonLD->{$labelsPropertyName} = $labelCollection->toStrings();
        }
    }

    /**
     * @param \CultureFeed_Cdb_Data_EventDetail $detail
     * @param \stdClass $jsonLD
     *
     * This is based on code found in the culturefeed theme.
     * @see https://github.com/cultuurnet/culturefeed/blob/master/culturefeed_agenda/theme/theme.inc#L266-L284
     */
    private function importPicture($detail, $jsonLD)
    {
        $mainPicture = null;

        // first check if there is a media file that is main and has the PHOTO media type
        $photos = $detail->getMedia()->byMediaType(CultureFeed_Cdb_Data_File::MEDIA_TYPE_PHOTO);
        foreach ($photos as $photo) {
            if ($photo->isMain()) {
                $mainPicture = $photo;
            }
        }

        // the IMAGEWEB media type is deprecated but can still be used as a main image if there is no PHOTO
        if (empty($mainPicture)) {
            $images = $detail->getMedia()->byMediaType(CultureFeed_Cdb_Data_File::MEDIA_TYPE_IMAGEWEB);
            foreach ($images as $image) {
                if ($image->isMain()) {
                    $mainPicture = $image;
                }
            }
        }

        // if there is no explicit main image we just use the oldest picture of any type
        if (empty($mainPicture)) {
            $pictures = $detail->getMedia()->byMediaTypes(
                [
                    CultureFeed_Cdb_Data_File::MEDIA_TYPE_PHOTO,
                    CultureFeed_Cdb_Data_File::MEDIA_TYPE_IMAGEWEB
                ]
            );

            $pictures->rewind();
            $mainPicture = count($pictures) > 0 ? $pictures->current() : null;
        }

        if ($mainPicture) {
            $jsonLD->image = $mainPicture->getHLink();
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

        $location_id = $this->cdbIdExtractor->getRelatedPlaceCdbId($event);

        if ($location_id) {
            $location += (array)$placeManager->placeJSONLD($location_id);
        } else {
            $location_cdb = $event->getLocation();
            $location['name']['nl'] = $location_cdb->getLabel();
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
        $organizer = null;
        $organizer_id = $this->cdbIdExtractor->getRelatedOrganizerCdbId($event);
        $organizer_cdb = $event->getOrganiser();
        $contact_info_cdb = $event->getContactInfo();

        if ($organizer_id) {
            $organizer = (array)$organizerManager->organizerJSONLD($organizer_id);
        } elseif ($organizer_cdb && $contact_info_cdb) {
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

        if (!is_null($organizer)) {
            $organizer['@type'] = 'Organizer';
            $jsonLD->organizer = $organizer;
        }
    }

    /**
     * @param \CultureFeed_Cdb_Data_EventDetail $detail
     * @param \stdClass $jsonLD
     */
    private function importPriceInfo(
        \CultureFeed_Cdb_Data_EventDetail $detail,
        $jsonLD
    ) {
        $prices = array();

        $price = $detail->getPrice();

        if ($price) {
            $description = $price->getDescription();

            if ($description) {
                $prices = $this->priceDescriptionParser->parse($description);
            }

            // If price description was not interpretable, fall back to
            // price title and value.
            if (empty($prices) && $price->getValue() !== null) {
                $prices['Basistarief'] = floatval($price->getValue());
            }
        }

        if (!empty($prices)) {
            $priceInfo = array();

            /** @var \CultureFeed_Cdb_Data_Price $price */
            foreach ($prices as $title => $value) {
                $priceInfoItem = array(
                    'name' => $title,
                    'priceCurrency' => 'EUR',
                    'price' => $value,
                );

                $priceInfoItem['category'] = 'tariff';

                if ($priceInfoItem['name'] === 'Basistarief') {
                    $priceInfoItem['category'] = 'base';
                }

                $priceInfo[] = $priceInfoItem;
            }

            if (!empty($priceInfo)) {
                $jsonLD->priceInfo = $priceInfo;
            }
        }
    }

    /**
     * @param \CultureFeed_Cdb_Data_EventDetail $detail
     * @param \stdClass $jsonLD
     */
    private function importBookingInfo(
        \CultureFeed_Cdb_Item_Event $event,
        \CultureFeed_Cdb_Data_EventDetail $detail,
        $jsonLD
    ) {
        $bookingInfo = array();

        $price = $detail->getPrice();
        if ($price) {
            if ($price->getDescription()) {
                $bookingInfo['description'] = $price->getDescription();
            }
            if ($price->getTitle()) {
                $bookingInfo['name'] = $price->getTitle();
            }
            if ($price->getValue() !== null) {
                $bookingInfo['priceCurrency'] = 'EUR';
                $bookingInfo['price'] = floatval($price->getValue());
            }
            if ($bookingPeriod = $event->getBookingPeriod()) {
                $startDate = $this->dateFromUdb2UnixTime($bookingPeriod->getDateFrom());
                $endDate = $this->dateFromUdb2UnixTime($bookingPeriod->getDateTill());

                $bookingInfo['availabilityStarts'] = $startDate->format('c');
                $bookingInfo['availabilityEnds'] = $endDate->format('c');
            }
        }

        // Add reservation contact data.
        $contactInfo = $event->getContactInfo();
        if ($contactInfo) {
            foreach ($contactInfo->getUrls() as $url) {
                if ($url->isForReservations()) {
                    $bookingInfo['url'] = $url->getUrl();
                    break;
                }
            }

            if (array_key_exists('url', $bookingInfo)) {
                $bookingInfo['urlLabel'] = 'Reserveer plaatsen';
            }

            foreach ($contactInfo->getPhones() as $phone) {
                if ($phone->isForReservations()) {
                    $bookingInfo['phone'] = $phone->getNumber();
                    break;
                }
            }

            foreach ($contactInfo->getMails() as $mail) {
                if ($mail->isForReservations()) {
                    $bookingInfo['email'] = $mail->getMailAddress();
                    break;
                }
            }
        }

        if (!empty($bookingInfo)) {
            $jsonLD->bookingInfo = $bookingInfo;
        }
    }

    /**
     * @param \CultureFeed_Cdb_Item_Event $event
     * @param \stdClass $jsonLD
     */
    private function importContactPoint(
        \CultureFeed_Cdb_Item_Event $event,
        \stdClass $jsonLD
    ) {
        $contactInfo = $event->getContactInfo();

        $notForReservations = function ($item) {
            /** @var \CultureFeed_Cdb_Data_Url|\CultureFeed_Cdb_Data_Phone|\CultureFeed_Cdb_Data_Mail $item */
            return !$item->isForReservations();
        };

        if ($contactInfo) {
            $contactPoint = array();

            $emails = array_filter($contactInfo->getMails(), $notForReservations);

            if (!empty($emails)) {
                $contactPoint['email'] = array_map(
                    function (\CultureFeed_Cdb_Data_Mail $email) {
                        return $email->getMailAddress();
                    },
                    $emails
                );
                $contactPoint['email'] = array_values($contactPoint['email']);
            }

            $phones = array_filter($contactInfo->getPhones(), $notForReservations);

            if (!empty($phones)) {
                $contactPoint['phone'] = array_map(
                    function (\CultureFeed_Cdb_Data_phone $phone) {
                        return $phone->getNumber();
                    },
                    $phones
                );
                $contactPoint['phone'] = array_values($contactPoint['phone']);
            }

            $urls = array_filter($contactInfo->getUrls(), $notForReservations);

            if (!empty($urls)) {
                $contactPoint['url'] = array_map(
                    function (\CultureFeed_Cdb_Data_Url $url) {
                        return $url->getUrl();
                    },
                    $urls
                );
                $contactPoint['url'] = array_values($contactPoint['url']);
            }

            array_filter($contactPoint);
            if (!empty($contactPoint)) {
                $jsonLD->contactPoint = $contactPoint;
            }
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
            $startDate = DateTimeFactory::dateTimeFromDateString($startDateString);

            if (iterator_count($calendar) > 1) {
                $periodArray = iterator_to_array($calendar);
                $lastCalendarItem = end($periodArray);
            } else {
                $lastCalendarItem = $firstCalendarItem;
            }

            $endDateString = $lastCalendarItem->getDateTo() . 'T00:00:00';
            $endDate = DateTimeFactory::dateTimeFromDateString($endDateString);

            $jsonLD->startDate = $startDate->format('c');
            $jsonLD->endDate = $endDate->format('c');
        } elseif ($calendar instanceof \CultureFeed_Cdb_Data_Calendar_TimestampList) {
            $calendarType = 'single';
            $calendar->rewind();
            /** @var \CultureFeed_Cdb_Data_Calendar_Timestamp $firstCalendarItem */
            $firstCalendarItem = $calendar->current();
            if ($firstCalendarItem->getStartTime()) {
                $dateString =
                    $firstCalendarItem->getDate() . 'T' . $firstCalendarItem->getStartTime();
            } else {
                $dateString = $firstCalendarItem->getDate() . 'T00:00:00';
            }

            $startDate = DateTimeFactory::dateTimeFromDateString($dateString);

            if (iterator_count($calendar) > 1) {
                $periodArray = iterator_to_array($calendar);
                $lastCalendarItem = end($periodArray);
            } else {
                $lastCalendarItem = $firstCalendarItem;
            }

            $endDateString = null;
            if ($lastCalendarItem->getEndTime()) {
                $endDateString =
                    $lastCalendarItem->getDate() . 'T' . $lastCalendarItem->getEndTime();
            } else {
                if (iterator_count($calendar) > 1) {
                    $endDateString = $lastCalendarItem->getDate() . 'T00:00:00';
                }
            }

            if ($endDateString) {
                $endDate = DateTimeFactory::dateTimeFromDateString($endDateString);
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

    /**
     * @param \CultureFeed_Cdb_Item_Event $event
     * @param \stdClass $jsonLD
     */
    private function importSeeAlso(
        \CultureFeed_Cdb_Item_Event $event,
        \stdClass $jsonLD
    ) {
        if (!property_exists($jsonLD, 'seeAlso')) {
            $jsonLD->seeAlso = [];
        }

        // Add contact info url, if it's not for reservations.
        if ($contactInfo = $event->getContactInfo()) {
            /** @var \CultureFeed_Cdb_Data_Url[] $contactUrls */
            $contactUrls = $contactInfo->getUrls();
            if (is_array($contactUrls) && count($contactUrls) > 0) {
                foreach ($contactUrls as $contactUrl) {
                    if (!$contactUrl->isForReservations()) {
                        $jsonLD->seeAlso[] = $contactUrl->getUrl();
                    }
                }
            }
        }
    }

    /**
     * @param \CultureFeed_Cdb_Item_Event $event
     * @param SluggerInterface $slugger
     * @param \stdClass $jsonLD
     */
    private function importUitInVlaanderenReference(
        \CultureFeed_Cdb_Item_Event $event,
        SluggerInterface $slugger,
        $jsonLD
    ) {

        // Some events seem to not have a Dutch name, even though this is
        // required. If there's no Dutch name, we just leave the slug empty as
        // that seems to be the behaviour on http://m.uitinvlaanderen.be
        if (isset($jsonLD->name['nl'])) {
            $name = $jsonLD->name['nl'];
            $slug = $slugger->slug($name);
        } else {
            $slug = '';
        }

        $reference = 'http://www.uitinvlaanderen.be/agenda/e/' . $slug . '/' . $event->getCdbId();


        if (!property_exists($jsonLD, 'sameAs')) {
            $jsonLD->sameAs = [];
        }

        if (!in_array($reference, $jsonLD->sameAs)) {
            array_push($jsonLD->sameAs, $reference);
        }
    }
}
