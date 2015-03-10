<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\UDB2;

use Broadway\Domain\AggregateRoot;
use Broadway\Domain\DomainEventStream;
use Broadway\Domain\DomainMessageInterface;
use Broadway\Domain\Metadata;
use Broadway\EventSourcing\EventStreamDecoratorInterface;
use Broadway\Repository\AggregateNotFoundException;
use Broadway\Repository\RepositoryInterface;
use CultureFeed_Cdb_Data_Address;
use CultureFeed_Cdb_Data_Address_PhysicalAddress;
use CultureFeed_Cdb_Data_Calendar_BookingPeriod;
use CultureFeed_Cdb_Data_Category;
use CultureFeed_Cdb_Data_CategoryList;
use CultureFeed_Cdb_Data_ContactInfo;
use CultureFeed_Cdb_Data_EventDetail;
use CultureFeed_Cdb_Data_EventDetailList;
use CultureFeed_Cdb_Data_Location;
use CultureFeed_Cdb_Data_Mail;
use CultureFeed_Cdb_Data_Organiser;
use CultureFeed_Cdb_Data_Phone;
use CultureFeed_Cdb_Data_Url;
use CultureFeed_Cdb_Default;
use CultureFeed_Cdb_Item_Event;
use CultuurNet\UDB3\Event\DescriptionTranslated;
use CultuurNet\UDB3\Event\Event;
use CultuurNet\UDB3\Event\Events\BookingInfoUpdated;
use CultuurNet\UDB3\Event\Events\ContactPointUpdated;
use CultuurNet\UDB3\Event\Events\DescriptionUpdated;
use CultuurNet\UDB3\Event\Events\EventCreated;
use CultuurNet\UDB3\Event\Events\OrganizerDeleted;
use CultuurNet\UDB3\Event\Events\OrganizerUpdated;
use CultuurNet\UDB3\Event\Events\TypicalAgeRangeUpdated;
use CultuurNet\UDB3\Event\EventWasTagged;
use CultuurNet\UDB3\Event\TagErased;
use CultuurNet\UDB3\Event\TitleTranslated;
use CultuurNet\UDB3\OrganizerService;
use CultuurNet\UDB3\PlaceService;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

/**
 * Repository decorator that first updates UDB2.
 *
 * When a failure on UDB2 occurs, the whole transaction will fail.
 */
class EventRepository implements RepositoryInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;
    use Udb2UtilityTrait;
    use \CultuurNet\UDB3\Udb3RepositoryTrait;

    /**
     * @var RepositoryInterface
     */
    protected $decoratee;

    /**
     * @var EntryAPIImprovedFactory
     */
    protected $entryAPIImprovedFactory;

    /**
     * @var boolean
     */
    protected $syncBack = false;

    /**
     * @var OrganizerService
     */
    protected $organizerService;
    /**
     * @var PlaceService
     */
    protected $placeService;

    /**
     * @var EventStreamDecoratorInterface[]
     */
    private $eventStreamDecorators = array();

    /**
     * @var EventImporterInterface
     */
    protected $eventImporter;

    private $aggregateClass;

    public function __construct(
        RepositoryInterface $decoratee,
        EntryAPIImprovedFactory $entryAPIImprovedFactory,
        EventImporterInterface $eventImporter,
        PlaceService $placeService,
        OrganizerService $organizerService,
        array $eventStreamDecorators = array()
    ) {
        $this->decoratee = $decoratee;
        $this->entryAPIImprovedFactory = $entryAPIImprovedFactory;
        $this->eventStreamDecorators = $eventStreamDecorators;
        $this->organizerService = $organizerService;
        $this->placeService = $placeService;
        $this->aggregateClass = Event::class;
        $this->eventImporter = $eventImporter;
    }

    public function syncBackOn()
    {
        $this->syncBack = true;
    }

    public function syncBackOff()
    {
        $this->syncBack = false;
    }

    /**
     * {@inheritdoc}
     */
    public function add(AggregateRoot $aggregate)
    {

        if ($this->syncBack) {
            // We can not directly act on the aggregate, as the uncommitted events will
            // be reset once we retrieve them, therefore we clone the object.
            $double = clone $aggregate;
            $domainEventStream = $double->getUncommittedEvents();
            $eventStream = $this->decorateForWrite(
                $aggregate,
                $domainEventStream
            );

            /** @var DomainMessageInterface $domainMessage */
            foreach ($eventStream as $domainMessage) {
                $domainEvent = $domainMessage->getPayload();

                switch (get_class($domainEvent)) {
                    case EventWasTagged::class:
                        /** @var EventWasTagged $domainEvent */
                        $this->applyEventWasTagged(
                            $domainEvent,
                            $domainMessage->getMetadata()
                        );
                        break;

                    case TagErased::class:
                        /** @var TagErased $domainEvent */
                        $this->applyTagErased(
                            $domainEvent,
                            $domainMessage->getMetadata()
                        );

                        break;

                    case TitleTranslated::class:
                        /** @var TitleTranslated $domainEvent */
                        $this->applyTitleTranslated(
                            $domainEvent,
                            $domainMessage->getMetadata()
                        );
                        break;

                    case DescriptionTranslated::class:
                        /** @var DescriptionTranslated $domainEvent */
                        $this->applyDescriptionTranslated(
                            $domainEvent,
                            $domainMessage->getMetadata()
                        );
                        break;

                    case EventCreated::class:
                        $this->applyEventCreated($domainEvent, $domainMessage->getMetadata());
                        break;

                    case DescriptionUpdated::class:
                        /** @var DescriptionUpdated $domainEvent */
                        $this->applyDescriptionUpdated(
                            $domainEvent,
                            $domainMessage->getMetadata()
                        );
                        break;

                    case TypicalAgeRangeUpdated::class:
                        /** @var TypicalAgeRangeUpdated $domainEvent */
                        $this->applyTypicalAgeRangeUpdated(
                            $domainEvent,
                            $domainMessage->getMetadata()
                        );
                        break;

                    case OrganizerUpdated::class:
                        $this->applyOrganizerUpdated(
                            $domainEvent,
                            $domainMessage->getMetadata()
                        );
                        break;

                    case OrganizerDeleted::class:
                        $this->applyOrganizerDeleted(
                            $domainEvent,
                            $domainMessage->getMetadata()
                        );

                        break;

                    case ContactPointUpdated::class:
                        $this->applyContactPointUpdated(
                            $domainEvent,
                            $domainMessage->getMetadata()
                        );
                        break;

                    case BookingInfoUpdated::class:
                        $this->applyBookingInfoUpdated(
                            $domainEvent,
                            $domainMessage->getMetadata()
                        );
                        break;

                    default:
                        // Ignore any other actions
                }
            }
        }

        $this->decoratee->add($aggregate);
    }

    private function applyEventWasTagged(
        EventWasTagged $tagged,
        Metadata $metadata
    ) {
        $this->createImprovedEntryAPIFromMetadata($metadata)
            ->addKeyword(
                $tagged->getEventId(),
                $tagged->getKeyword()
            );
    }

    private function applyTagErased(
        TagErased $tagErased,
        Metadata $metadata
    ) {
        $this->createImprovedEntryAPIFromMetadata($metadata)
            ->deleteKeyword(
                $tagErased->getEventId(),
                $tagErased->getKeyword()
            );
    }

    private function applyTitleTranslated(
        TitleTranslated $domainEvent,
        Metadata $metadata
    ) {
        $this->createImprovedEntryAPIFromMetadata($metadata)
            ->translateEventTitle(
                $domainEvent->getEventId(),
                $domainEvent->getLanguage(),
                $domainEvent->getTitle()
            );
    }

    private function applyDescriptionTranslated(
        DescriptionTranslated $domainEvent,
        Metadata $metadata
    ) {
        $this->createImprovedEntryAPIFromMetadata($metadata)
            ->translateEventDescription(
                $domainEvent->getEventId(),
                $domainEvent->getLanguage(),
                $domainEvent->getDescription()
            );
    }

    private function decorateForWrite(
        AggregateRoot $aggregate,
        DomainEventStream $eventStream
    ) {
        $aggregateType = $this->getType();
        $aggregateIdentifier = $aggregate->getAggregateRootId();

        foreach ($this->eventStreamDecorators as $eventStreamDecorator) {
            $eventStream = $eventStreamDecorator->decorateForWrite(
                $aggregateType,
                $aggregateIdentifier,
                $eventStream
            );
        }

        return $eventStream;
    }

    /**
     * {@inheritdoc}
     *
     * Ensures an event is created, by importing it from UDB2 if it does not
     * exist locally yet.
     */
    public function load($id)
    {
        try {
            $event = $this->decoratee->load($id);
        } catch (AggregateNotFoundException $e) {
            $event = $this->eventImporter->createEventFromUDB2($id);

            if (!$event) {
                throw new AggregateNotFoundException($id);
            }
        }

        return $event;
    }

    /**
     * Listener on the eventCreated event. Send a new event also to UDB2.
     */
    public function applyEventCreated(EventCreated $eventCreated, Metadata $metadata)
    {

        $event = new CultureFeed_Cdb_Item_Event();
        $event->setCdbId($eventCreated->getEventId());

        $nlDetail = new CultureFeed_Cdb_Data_EventDetail();
        $nlDetail->setLanguage('nl');
        $nlDetail->setTitle($eventCreated->getTitle());

        $details = new CultureFeed_Cdb_Data_EventDetailList();
        $details->add($nlDetail);
        $event->setDetails($details);

        // Set location and calendar info.
        $this->setLocationForEventCreated($eventCreated, $event);
        $this->setCalendarForItemCreated($eventCreated, $event);

        // Set event type and theme.
        $event->setCategories(new CultureFeed_Cdb_Data_CategoryList());
        $eventType = new CultureFeed_Cdb_Data_Category(
            'eventtype',
            $eventCreated->getEventType()->getId(),
            $eventCreated->getEventType()->getLabel()
        );
        $event->getCategories()->add($eventType);

        if ($eventCreated->getTheme() !== null) {
            $theme = new CultureFeed_Cdb_Data_Category(
                'theme',
                $eventCreated->getTheme()->getId(),
                $eventCreated->getTheme()->getLabel()
            );
            $event->getCategories()->add($theme);
        }

        // Empty contact info.
        $contactInfo = new CultureFeed_Cdb_Data_ContactInfo();
        $event->setContactInfo($contactInfo);

        $cdbXml = new CultureFeed_Cdb_Default();
        $cdbXml->addItem($event);

        $this->createImprovedEntryAPIFromMetadata($metadata)
            ->createEvent((string)$cdbXml);

        return $eventCreated->getEventId();
    }

    /**
     * Send the updated description also to CDB2.
     */
    private function applyDescriptionUpdated(
        DescriptionUpdated $descriptionUpdated,
        Metadata $metadata
    ) {

        $entryApi = $this->createImprovedEntryAPIFromMetadata($metadata);
        $event = $entryApi->getEvent($descriptionUpdated->getEventId());

        $event->getDetails()->getDetailByLanguage('nl')->setLongDescription($descriptionUpdated->getDescription());

        $entryApi->updateEvent($event);

    }

    /**
     * Send the updated age range also to CDB2.
     */
    private function applyTypicalAgeRangeUpdated(
        TypicalAgeRangeUpdated $ageRangeUpdated,
        Metadata $metadata
    ) {

        $entryApi = $this->createImprovedEntryAPIFromMetadata($metadata);
        $event = $entryApi->getEvent($ageRangeUpdated->getEventId());

        $ages = explode('-', $ageRangeUpdated->getTypicalAgeRange());
        $event->setAgeFrom($ages[0]);

        $entryApi->updateEvent($event);

    }

    /**
     * Apply the organizer updated event to the event repository.
     * @param OrganizerUpdated $organizerUpdated
     */
    private function applyOrganizerUpdated(
        OrganizerUpdated $domainEvent,
        Metadata $metadata
    ) {

        $organizerJSONLD = $this->organizerService->getEntity(
            $domainEvent->getOrganizerId()
        );

        $organizer = json_decode($organizerJSONLD);

        $entryApi = $this->createImprovedEntryAPIFromMetadata($metadata);
        $event = $entryApi->getEvent($domainEvent->getEventId());

        $cdbOrganizer = new CultureFeed_Cdb_Data_Organiser();
        $cdbOrganizer->setLabel($organizer->name);
        $event->setOrganiser($cdbOrganizer);

        $entryApi->updateEvent($event);

    }

    /**
     * Delete the organizer also in cdb.
     *
     * @param OrganizerDeleted $domainEvent
     * @param Metadata $metadata
     */
    private function applyOrganizerDeleted(
        OrganizerDeleted $domainEvent,
        Metadata $metadata
    ) {

        $entryApi = $this->createImprovedEntryAPIFromMetadata($metadata);
        $event = $entryApi->getEvent($domainEvent->getEventId());
        $event->deleteOrganiser();

        $entryApi->updateEvent($event);

    }

    /**
     * Updated the contact info in udb2.
     *
     * @param ContactPointUpdated $domainEvent
     * @param Metadata $metadata
     */
    private function applyContactPointUpdated(
        ContactPointUpdated $domainEvent,
        Metadata $metadata
    ) {

        $entryApi = $this->createImprovedEntryAPIFromMetadata($metadata);
        $event = $entryApi->getEvent($domainEvent->getEventId());
        $contactPoint = $domainEvent->getContactPoint();

        $contactInfo = $event->getContactInfo();
        $contactInfo->deletePhones();

        $phones = $contactPoint->getPhones();
        foreach ($phones as $phone) {
            $contactInfo->addPhone(new CultureFeed_Cdb_Data_Phone($phone));
        }

        $contactInfo->deleteUrls();
        $urls = $contactPoint->getUrls();
        foreach ($urls as $url) {
            $contactInfo->addUrl(new CultureFeed_Cdb_Data_Url($url));
        }

        $contactInfo->deleteMails();
        $emails = $contactPoint->getEmails();
        foreach ($emails as $email) {
            $contactInfo->addMail(new CultureFeed_Cdb_Data_Mail($email));
        }
        $event->setContactInfo($contactInfo);

        $entryApi->updateEvent($event);

    }

    /**
     * Updated the booking info in udb2.
     *
     * @param BookingInfoUpdated $domainEvent
     * @param Metadata $metadata
     */
    private function applyBookingInfoUpdated(
        BookingInfoUpdated $domainEvent,
        Metadata $metadata
    ) {

        $entryApi = $this->createImprovedEntryAPIFromMetadata($metadata);
        $event = $entryApi->getEvent($domainEvent->getEventId());
        $bookingInfo = $domainEvent->getBookingInfo();
        
        $bookingPeriod = $event->getBookingPeriod();
        if (empty($bookingPeriod)) {
          $bookingPeriod = new CultureFeed_Cdb_Data_Calendar_BookingPeriod();
        }
        
        if (!empty($bookingInfo->availabilityStarts)) {
          $bookingPeriod->setDateFrom($bookingInfo->availabilityStarts);
        }
        if (!empty($bookingInfo->availabilityEnds)) {
          $bookingPeriod->setDateTill($bookingInfo->availabilityEnds);
        }
        $event->setBookingPeriod($bookingPeriod);
          
        $entryApi->updateEvent($event);

    }

    /**
     * Set the location on the cdb event based on an eventCreated event.
     *
     * @param EventCreated $eventCreated
     * @param CultureFeed_Cdb_Item_Event $cdbEvent
     */
    private function setLocationForEventCreated(EventCreated $eventCreated, CultureFeed_Cdb_Item_Event $cdbEvent)
    {

        $placeEntity = $this->placeService->getEntity($eventCreated->getLocation()->getCdbid());
        $place = json_decode($placeEntity);

        $eventLocation = $eventCreated->getLocation();

        $physicalAddress = new CultureFeed_Cdb_Data_Address_PhysicalAddress();
        $physicalAddress->setCountry($place->address->addressCountry);
        $physicalAddress->setCity($place->address->addressLocality);
        $physicalAddress->setZip($place->address->postalCode);


        // @todo This is not an exact mapping, because we do not have a separate
        // house number in JSONLD, this should be fixed somehow. Probably it's
        // better to use another read model than JSON-LD for this purpose.
        $streetParts = explode(' ', $place->address->streetAddress);

        if (count($streetParts) > 1) {
            $number = array_pop($streetParts);
            $physicalAddress->setStreet(implode(' ', $streetParts));
            $physicalAddress->setHouseNumber($number);
        } else {
            $physicalAddress->setStreet($eventLocation->getStreet());
        }

        $address = new CultureFeed_Cdb_Data_Address($physicalAddress);

        $location = new CultureFeed_Cdb_Data_Location($address);
        $location->setLabel($eventLocation->getName());
        $cdbEvent->setLocation($location);

    }
}
