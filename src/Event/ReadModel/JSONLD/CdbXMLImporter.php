<?php

namespace CultuurNet\UDB3\Event\ReadModel\JSONLD;

use CultuurNet\UDB3\CalendarFactoryInterface;
use CultuurNet\UDB3\Cdb\CdbId\EventCdbIdExtractorInterface;
use CultuurNet\UDB3\Cdb\PriceDescriptionParser;
use CultuurNet\UDB3\Event\ValueObjects\Audience;
use CultuurNet\UDB3\Event\ValueObjects\AudienceType;
use CultuurNet\UDB3\LabelImporter;
use CultuurNet\UDB3\Offer\ReadModel\JSONLD\CdbXmlContactInfoImporterInterface;
use CultuurNet\UDB3\Offer\ReadModel\JSONLD\CdbXMLItemBaseImporter;
use CultuurNet\UDB3\SluggerInterface;
use CultuurNet\UDB3\StringFilter\BreakTagToNewlineStringFilter;
use CultuurNet\UDB3\StringFilter\CombinedStringFilter;
use CultuurNet\UDB3\StringFilter\ConsecutiveBlockOfTextStringFilter;
use CultuurNet\UDB3\StringFilter\StringFilterInterface;
use CultuurNet\UDB3\StringFilter\StripSourceStringFilter;
use CultuurNet\UDB3\StringFilter\StripSurroundingSpaceStringFilter;

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
     * @var StringFilterInterface
     */
    private $longDescriptionFilter;

    /**
     * @var StringFilterInterface
     */
    private $shortDescriptionFilter;

    /**
     * @var CalendarFactoryInterface
     */
    private $calendarFactory;

    /**
     * @var CdbXmlContactInfoImporterInterface
     */
    private $cdbXmlContactInfoImporter;

    /**
     * @param CdbXMLItemBaseImporter $cdbXMLItemBaseImporter
     * @param EventCdbIdExtractorInterface $cdbIdExtractor
     * @param PriceDescriptionParser $priceDescriptionParser
     * @param CalendarFactoryInterface $calendarFactory
     * @param CdbXmlContactInfoImporterInterface $cdbXmlContactInfoImporter
     */
    public function __construct(
        CdbXMLItemBaseImporter $cdbXMLItemBaseImporter,
        EventCdbIdExtractorInterface $cdbIdExtractor,
        PriceDescriptionParser $priceDescriptionParser,
        CalendarFactoryInterface $calendarFactory,
        CdbXmlContactInfoImporterInterface $cdbXmlContactInfoImporter
    ) {
        $this->cdbXMLItemBaseImporter = $cdbXMLItemBaseImporter;
        $this->cdbIdExtractor = $cdbIdExtractor;
        $this->priceDescriptionParser = $priceDescriptionParser;
        $this->calendarFactory = $calendarFactory;
        $this->cdbXmlContactInfoImporter = $cdbXmlContactInfoImporter;

        $consecutiveBlockOfTextFilter = new ConsecutiveBlockOfTextStringFilter();

        $this->longDescriptionFilter = new CombinedStringFilter();
        $this->longDescriptionFilter->addFilter(
            new StripSourceStringFilter()
        );
        $this->longDescriptionFilter->addFilter(
            $consecutiveBlockOfTextFilter
        );
        $this->longDescriptionFilter->addFilter(
            new BreakTagToNewlineStringFilter()
        );
        $this->longDescriptionFilter->addFilter(
            new StripSurroundingSpaceStringFilter()
        );

        $this->shortDescriptionFilter = $consecutiveBlockOfTextFilter;
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

        $labelImporter = new LabelImporter();
        $labelImporter->importLabels($event, $jsonLD);

        $jsonLD->calendarSummary = $detail->getCalendarSummary();

        $this->importLocation($event, $placeManager, $jsonLD);

        $this->importOrganizer($event, $organizerManager, $jsonLD);

        if ($event->getContactInfo()) {
            $this->cdbXmlContactInfoImporter->importBookingInfo(
                $jsonLD,
                $event->getContactInfo(),
                $detail->getPrice(),
                $event->getBookingPeriod()
            );

            $this->cdbXmlContactInfoImporter->importContactPoint(
                $jsonLD,
                $event->getContactInfo()
            );
        }

        $this->importPriceInfo($detail, $jsonLD);

        $this->importTerms($event, $jsonLD);

        $this->cdbXMLItemBaseImporter->importPublicationInfo($event, $jsonLD);

        $calendar = $this->calendarFactory->createFromCdbCalendar($event->getCalendar());
        $jsonLD = (object)array_merge((array)$jsonLD, $calendar->toJsonLd());

        $this->importTypicalAgeRange($event, $jsonLD);

        $this->importPerformers($detail, $jsonLD);

        $this->importLanguages($event, $jsonLD);

        $this->importUitInVlaanderenReference($event, $slugger, $jsonLD);

        $this->cdbXMLItemBaseImporter->importExternalId($event, $jsonLD);

        $this->importSeeAlso($event, $jsonLD);

        $this->cdbXMLItemBaseImporter->importWorkflowStatus($event, $jsonLD);

        $this->importAudience($event, $jsonLD);

        return $jsonLD;
    }

    /**
     * @param \CultureFeed_Cdb_Data_EventDetail $languageDetail
     * @param \stdClass $jsonLD
     * @param string $language
     */
    private function importDescription($languageDetail, $jsonLD, $language)
    {
        $longDescription = $languageDetail->getLongDescription();

        if ($longDescription) {
            $longDescription = $this->longDescriptionFilter->filter(
                $longDescription
            );
        }

        $descriptions = [];

        $shortDescription = $languageDetail->getShortDescription();
        if ($shortDescription) {
            $includeShortDescription = true;

            $shortDescription = $this->shortDescriptionFilter->filter(
                $shortDescription
            );

            if ($longDescription) {
                $includeShortDescription =
                    !$this->longDescriptionStartsWithShortDescription(
                        $longDescription,
                        $shortDescription
                    );
            }

            if ($includeShortDescription) {
                $descriptions[] = $shortDescription;
            }
        }

        if ($longDescription) {
            $descriptions[] = $longDescription;
        }

        $description = implode("\n\n", $descriptions);

        $jsonLD->description[$language] = $description;
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

            /** @var \CultureFeed_Cdb_Data_Phone[] $phones_cdb */
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
    private function importTypicalAgeRange(\CultureFeed_Cdb_Item_Event $event, $jsonLD)
    {
        $ageFrom = $event->getAgeFrom();

        if (isset($ageFrom) && is_int($ageFrom)) {
            if ($ageFrom <= 12) {
                $jsonLD->typicalAgeRange = "{$ageFrom}-12";
            } else if ($ageFrom <= 18) {
                $jsonLD->typicalAgeRange = "{$ageFrom}-18";
            } else if ($ageFrom <= 99) {
                $jsonLD->typicalAgeRange = "{$ageFrom}-99";
            } else {
                $jsonLD->typicalAgeRange = '99-99';
            }
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

    /**
     * @param \CultureFeed_Cdb_Item_Event $event
     * @param \stdClass $jsonLD
     */
    private function importAudience(\CultureFeed_Cdb_Item_Event $event, \stdClass $jsonLD)
    {
        $eventIsPrivate = !!$event->isPrivate();
        $evenTargetsEducation = $eventIsPrivate && $event->getCategories()->hasCategory('2.1.3.0.0');

        $audienceType = $evenTargetsEducation ? 'education' : ($eventIsPrivate ? 'members' : 'everyone');
        $audience = new Audience(AudienceType::fromNative($audienceType));

        $jsonLD->audience = $audience->serialize();
    }

    /**
     * @param string $longDescription
     * @param string $shortDescription
     * @return bool
     */
    private function longDescriptionStartsWithShortDescription(
        $longDescription,
        $shortDescription
    ) {
        $longDescription = strip_tags(html_entity_decode($longDescription));

        return 0 === strncmp($longDescription, $shortDescription, mb_strlen($shortDescription));
    }
}
