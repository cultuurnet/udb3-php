<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Event;

use Broadway\ReadModel\Projector;
use CultuurNet\UDB3\Cdb\EventItemFactory;
use CultuurNet\UDB3\CulturefeedSlugger;
use CultuurNet\UDB3\EntityNotFoundException;
use CultuurNet\UDB3\Event\ReadModel\JsonDocument;
use CultuurNet\UDB3\Event\ReadModel\DocumentRepositoryInterface;
use CultuurNet\UDB3\EventServiceInterface;
use CultuurNet\UDB3\Iri\IriGeneratorInterface;
use CultuurNet\UDB3\OrganizerService;
use CultuurNet\UDB3\Place\PlaceProjectedToJSONLD;
use CultuurNet\UDB3\PlaceService;

class EventLDProjector extends Projector
{
    /**
     * @var DocumentRepositoryInterface
     */
    protected $repository;

    /**
     * @var IriGeneratorInterface
     */
    protected $iriGenerator;

    /**
     * @var OrganizerService
     */
    protected $organizerService;

    /**
     * @var PlaceService
     */
    protected $placeService;

    /**
     * @var EventServiceInterface
     */
    protected $eventService;

    /**
     * @var  SluggerInterface
     */
    protected $slugger;


    /**
     * @param DocumentRepositoryInterface $repository
     * @param IriGeneratorInterface $iriGenerator
     * @param EventServiceInterface $eventService
     * @param PlaceService $placeService
     * @param OrganizerService $organiserService
     */
    public function __construct(
        DocumentRepositoryInterface $repository,
        IriGeneratorInterface $iriGenerator,
        EventServiceInterface $eventService,
        PlaceService $placeService,
        OrganizerService $organiserService
    ) {
        $this->repository = $repository;
        $this->iriGenerator = $iriGenerator;
        $this->organizerService = $organiserService;
        $this->placeService = $placeService;
        $this->eventService = $eventService;

        $this->slugger = new CulturefeedSlugger();
    }

    /**
     * @param $dateString
     * @return \DateTime
     */
    public function dateFromUdb2DateString($dateString)
    {
        return \DateTime::createFromFormat(
            'Y-m-d?H:i:s',
            $dateString,
            new \DateTimeZone('Europe/Brussels')
        );
    }

    protected function applyOrganizerProjectedToJSONLD()
    {
        // @todo get events linked to this organizer, and update their JSON-LD
        // representation
    }

    protected function applyPlaceProjectedToJSONLD(
        PlaceProjectedToJSONLD $placeProjectedToJSONLD
    ) {
        $eventsLocatedAtPlace = $this->eventsLocatedAtPlace(
            $placeProjectedToJSONLD->getId()
        );

        $placeJSONLD = $this->placeService->getEntity(
            $placeProjectedToJSONLD->getId()
        );

        foreach ($eventsLocatedAtPlace as $eventId) {
            $document = $this->loadDocumentFromRepositoryByEventId(
                $eventId
            );
            $eventLD = $document->getBody();
            $eventLD->place = json_decode($placeJSONLD);
        }
    }

    /**
     * @param string $organizerId
     * @return string[]
     */
    protected function eventsOrganizedByOrganizer($organizerId)
    {
        return $this->eventService->eventsOrganizedByOrganizer(
            $organizerId
        );
    }

    /**
     * @param string $placeId
     * @return string[]
     */
    protected function eventsLocatedAtPlace($placeId)
    {
        return $this->eventService->eventsLocatedAtPlace(
            $placeId
        );
    }

    /**
     * @param EventImportedFromUDB2 $eventImportedFromUDB2
     */
    public function applyEventImportedFromUDB2(
        EventImportedFromUDB2 $eventImportedFromUDB2
    ) {
        $udb2Event = EventItemFactory::createEventFromCdbXml(
            $eventImportedFromUDB2->getCdbXmlNamespaceUri(),
            $eventImportedFromUDB2->getCdbXml()
        );

        $document = $this->newDocument($eventImportedFromUDB2->getEventId());
        $eventLd = $document->getBody();

        /** @var \CultureFeed_Cdb_Data_EventDetail $detail */
        $detail = null;

        /** @var \CultureFeed_Cdb_Data_EventDetail[] $details */
        $details = $udb2Event->getDetails();

        foreach ($details as $languageDetail) {
            $language = $languageDetail->getLanguage();

            // The first language detail found will be used to retrieve
            // properties from which in UDB3 are not any longer considered
            // to be language specific.
            if (!$detail) {
                $detail = $languageDetail;
            }

            $eventLd->name[$language] = $languageDetail->getTitle();

            $descriptions = [
                $languageDetail->getShortDescription(),
                $languageDetail->getLongDescription()
            ];
            $descriptions = array_filter($descriptions);
            $eventLd->description[$language] = implode('<br/>', $descriptions);
        }

        $pictures = $detail->getMedia()->byMediaType(
            \CultureFeed_Cdb_Data_File::MEDIA_TYPE_PHOTO
        );

        $pictures->rewind();
        $picture = count($pictures) > 0 ? $pictures->current() : null;

        $keywords = array_filter(
            array_values($udb2Event->getKeywords()),
            function ($keyword) {
                return (strlen(trim($keyword)) > 0);
            }
        );

        $eventLd->keywords = $keywords;
        $eventLd->calendarSummary = $detail->getCalendarSummary();
        $eventLd->image = $picture ? $picture->getHLink() : null;

        // Location.
        $location = array();
        $location['@type'] = 'Place';

        $location_cdb = $udb2Event->getLocation();
        $location_id = $location_cdb->getCdbid();

        if ($location_id) {
            $location += (array)$this->placeJSONLD($location_id);
        } else {
            $location['name'] = $location_cdb->getLabel();
            $address = $location_cdb->getAddress()->getPhysicalAddress();
            if ($address) {
                $location['address'] = array(
                    'addressCountry' => $address->getCountry(),
                    'addressLocality' => $address->getCity(),
                    'postalCode' => $address->getZip(),
                    'streetAddress' => $address->getStreet(
                        ) . ' ' . $address->getHouseNumber(),
                );
            }
        }
        $eventLd->location = $location;

        // Organizer.
        $organizer_cdb = $udb2Event->getOrganiser();
        $contact_info_cdb = $udb2Event->getContactInfo();

        if ($organizer_cdb && $contact_info_cdb) {
            $organizer_id = $organizer_cdb->getCdbid();
            if ($organizer_id) {
                $organizer['@id'] = $this->organizerService->iri($organizer_id);
            } else {
                $organizer = array();
                $organizer['name'] = $organizer_cdb->getLabel();
                $organizer['email'] = array();
                $mails = $contact_info_cdb->getMails();
                foreach ($mails as $mail) {
                    $organizer['email'][] = $mail->getMailAddress();
                }
                $organizer['phone'] = array();
                /** @var \CultureFeed_Cdb_Data_Phone[] $phones */
                $phones = $contact_info_cdb->getPhones();
                foreach ($phones as $phone) {
                    $organizer['phone'][] = $phone->getNumber();
                }
            }
            $eventLd->organizer = $organizer;
        }


        $price = $detail->getPrice();

        if ($price) {
            $eventLd->bookingInfo = array();
            // Booking info.
            $bookingInfo = array(
                'priceCurrency' => 'EUR',
            );
            $bookingInfo['description'] = $price->getDescription();
            $bookingInfo['name'] = $price->getTitle();
            $bookingInfo['price'] = floatval($price->getValue());
            $eventLd->bookingInfo[] = $bookingInfo;
        }

        // Input info.
        $eventLd->creator = $udb2Event->getCreatedBy();

        // Terms.
        $themeBlacklist = [
            'Thema onbepaald',
            'Meerder kunstvormen',
            'Meerdere filmgenres'
        ];
        $categories = array();
        foreach ($udb2Event->getCategories() as $category) {
            /* @var \Culturefeed_Cdb_Data_Category $category */
            if ($category && !in_array($category->getName(), $themeBlacklist)) {
                $categories[] = array(
                    'label' => $category->getName(),
                    'domain' => $category->getType(),
                    'id' => $category->getId(),
                );
            }
        }
        $eventLd->terms = $categories;

        // format using ISO-8601 with time zone designator
        $creationDate = $this->dateFromUdb2DateString(
            $udb2Event->getCreationDate()
        );
        $eventLd->created = $creationDate->format('c');

        $eventLd->publisher = $udb2Event->getOwner();


        // Calendar info
        // To render the front-end we make a distinction between 4 calendar types
        // Permanent and Periodic map directly to the Cdb calendar classes
        // Simple timestamps are divided into single and multiple
        $calendarType = 'unknown';
        $calendar = $udb2Event->getCalendar();

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

            $eventLd->startDate = $startDate->format('c');
            $eventLd->endDate = $endDate->format('c');
        } elseif ($calendar instanceof \CultureFeed_Cdb_Data_Calendar_TimestampList) {
            $calendarType = 'single';
            $calendar->rewind();
            $firstCalendarItem = $calendar->current();
            if ($firstCalendarItem->getStartTime()) {
                $dateString = $firstCalendarItem->getDate(
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
                $endDateString = $lastCalendarItem->getDate(
                    ) . 'T' . $lastCalendarItem->getEndTime();
            } else {
                if (iterator_count($calendar) > 1) {
                    $endDateString = $lastCalendarItem->getDate() . 'T00:00:00';
                }
            }

            if ($endDateString) {
                $endDate = $this->dateFromUdb2DateString($endDateString);
                $eventLd->endDate = $endDate->format('c');

                if ($startDate->format('Ymd') != $endDate->format('Ymd')) {
                    $calendarType = 'multiple';
                }
            }

            $eventLd->startDate = $startDate->format('c');
        }

        $eventLd->calendarType = $calendarType;

        $eventSlug = $this->slugger->slug(reset($eventLd->name));
        $eventLd->sameAs = array(
            'http://www.uitinvlaanderen.be/agenda/e/' . $eventSlug . '/' . $eventImportedFromUDB2->getEventId(),
        );

        $eventLdModel = new JsonDocument(
            $eventImportedFromUDB2->getEventId()
        );

        $this->repository->save($eventLdModel->withBody($eventLd));
    }

    /**
     * @param EventCreated $eventCreated
     */
    protected function applyEventCreated(EventCreated $eventCreated)
    {
        $document = $this->newDocument($eventCreated->getEventId());

        $jsonLD = $document->getBody();

        $jsonLD->{'@id'} = $this->iriGenerator->iri($eventCreated->getEventId());
        $jsonLD->name['nl'] = $eventCreated->getTitle();
        $jsonLD->location = array(
            '@type' => 'Place',
        ) + (array)$this->placeJSONLD($eventCreated->getLocation());


        $jsonLD->calendarType = 'single';
        $jsonLD->startDate = $eventCreated->getDate()->format('c');

        $this->repository->save($document->withBody($jsonLD));
    }

    protected function placeJSONLD($placeId)
    {
        try {
            $placeJSONLD = $this->placeService->getEntity(
                $placeId
            );

            return json_decode($placeJSONLD);
        } catch (EntityNotFoundException $e) {
            // In case the place can not be found at the moment, just add its ID
            return array(
                '@id' => $this->placeService->iri($placeId)
            );
        }
    }

    /**
     * @param EventWasTagged $eventTagged
     */
    protected function applyEventWasTagged(EventWasTagged $eventTagged)
    {
        $document = $this->loadDocumentFromRepository($eventTagged);

        $eventLd = $document->getBody();
        // TODO: Check if the event is already tagged with the keyword?
        $eventLd->concept[] = (string)$eventTagged->getKeyword();

        $this->repository->save($document->withBody($eventLd));
    }

    public function applyTagErased(TagErased $tagErased)
    {
        $document = $this->loadDocumentFromRepository($tagErased);

        $eventLd = $document->getBody();

        $eventLd->concept = array_filter(
            $eventLd->concept,
            function ($keyword) use ($tagErased) {
                return $keyword !== (string)$tagErased->getKeyword();
            }
        );
        // Ensure array keys start with 0 so json_encode() does encode it
        // as an array and not as an object.
        $eventLd->concept = array_values($eventLd->concept);

        $this->repository->save($document->withBody($eventLd));
    }

    protected function applyTitleTranslated(TitleTranslated $titleTranslated)
    {
        $document = $this->loadDocumentFromRepository($titleTranslated);

        $eventLd = $document->getBody();
        $eventLd->name->{$titleTranslated->getLanguage()->getCode(
        )} = $titleTranslated->getTitle();

        $this->repository->save($document->withBody($eventLd));
    }

    protected function applyDescriptionTranslated(
        DescriptionTranslated $descriptionTranslated
    ) {
        $document = $this->loadDocumentFromRepository($descriptionTranslated);

        $eventLd = $document->getBody();
        $eventLd->description->{$descriptionTranslated->getLanguage()->getCode(
        )} = $descriptionTranslated->getDescription();

        $this->repository->save($document->withBody($eventLd));
    }

    /**
     * @param string $id
     * @return JsonDocument
     */
    protected function newDocument($id)
    {
        $document = new JsonDocument($id);

        $eventLd = $document->getBody();
        $eventLd->{'@id'} = $this->iriGenerator->iri($id);

        // @todo provide Event-LD context here relative to the base URI
        $eventLd->{'@context'} = '/api/1.0/event.jsonld';

        return $document->withBody($eventLd);
    }

    /**
     * @param EventEvent $event
     * @return JsonDocument
     */
    protected function loadDocumentFromRepository(EventEvent $event)
    {
        return $this->loadDocumentFromRepositoryByEventId($event->getEventId());
    }

    /**
     * @param string $eventId
     * @return JsonDocument
     */
    protected function loadDocumentFromRepositoryByEventId($eventId)
    {
        $document = $this->repository->get($eventId);

        if (!$document) {
            return $this->newDocument($eventId);
        }

        return $document;
    }
}
