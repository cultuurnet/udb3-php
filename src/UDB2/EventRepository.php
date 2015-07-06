<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\UDB2;

use Broadway\Domain\AggregateRoot;
use Broadway\Domain\DomainEventStream;
use Broadway\Domain\DomainMessage;
use Broadway\Domain\Metadata;
use Broadway\EventSourcing\EventStreamDecoratorInterface;
use Broadway\Repository\AggregateNotFoundException;
use Broadway\Repository\RepositoryInterface;
use CultureFeed_Cdb_Data_Address;
use CultureFeed_Cdb_Data_Address_PhysicalAddress;
use CultureFeed_Cdb_Data_Category;
use CultureFeed_Cdb_Data_CategoryList;
use CultureFeed_Cdb_Data_ContactInfo;
use CultureFeed_Cdb_Data_EventDetail;
use CultureFeed_Cdb_Data_EventDetailList;
use CultureFeed_Cdb_Data_Location;
use CultureFeed_Cdb_Default;
use CultureFeed_Cdb_Item_Event;
use CultuurNet\Entry\BookingPeriod;
use CultuurNet\Entry\EntityType;
use CultuurNet\Entry\Language;
use CultuurNet\Entry\Number;
use CultuurNet\Entry\String;
use CultuurNet\UDB3\Event\DescriptionTranslated;
use CultuurNet\UDB3\Event\Event;
use CultuurNet\UDB3\Event\Events\BookingInfoUpdated;
use CultuurNet\UDB3\Event\Events\ContactPointUpdated;
use CultuurNet\UDB3\Event\Events\DescriptionUpdated;
use CultuurNet\UDB3\Event\Events\EventCreated;
use CultuurNet\UDB3\Event\Events\EventDeleted;
use CultuurNet\UDB3\Event\Events\EventWasLabelled;
use CultuurNet\UDB3\Event\Events\ImageAdded;
use CultuurNet\UDB3\Event\Events\ImageDeleted;
use CultuurNet\UDB3\Event\Events\ImageUpdated;
use CultuurNet\UDB3\Event\Events\MajorInfoUpdated;
use CultuurNet\UDB3\Event\Events\OrganizerDeleted;
use CultuurNet\UDB3\Event\Events\OrganizerUpdated;
use CultuurNet\UDB3\Event\Events\TypicalAgeRangeDeleted;
use CultuurNet\UDB3\Event\Events\TypicalAgeRangeUpdated;
use CultuurNet\UDB3\Event\Events\Unlabelled;
use CultuurNet\UDB3\Event\TitleTranslated;
use CultuurNet\UDB3\OrganizerService;
use CultuurNet\UDB3\PlaceService;
use CultuurNet\UDB3\Location;
use CultuurNet\UDB3\Udb3RepositoryTrait;
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
    use Udb3RepositoryTrait;

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
    public function save(AggregateRoot $aggregate)
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

            /** @var DomainMessage $domainMessage */
            foreach ($eventStream as $domainMessage) {
                $domainEvent = $domainMessage->getPayload();

                switch (get_class($domainEvent)) {
                    case EventWasLabelled::class:
                        /** @var EventWasLabelled $domainEvent */
                        $this->applyEventWasLabelled(
                            $domainEvent,
                            $domainMessage->getMetadata()
                        );
                        break;

                    case Unlabelled::class:
                        /** @var Unlabelled $domainEvent */
                        $this->applyUnlabelled(
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
                        $this->applyEventCreated(
                            $domainEvent,
                            $domainMessage->getMetadata()
                        );
                        break;

                    case EventDeleted::class:
                        $this->applyEventDeleted(
                            $domainEvent,
                            $domainMessage->getMetadata()
                        );
                        break;

                    case MajorInfoUpdated::class:
                        $this->applyMajorInfoUpdated(
                            $domainEvent,
                            $domainMessage->getMetadata()
                        );
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

                    case TypicalAgeRangeDeleted::class:
                        /** @var TypicalAgeRangeDeleted $domainEvent */
                        $this->applyTypicalAgeRangeDeleted(
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

                    case ImageAdded::class:
                        $this->applyImageAdded(
                            $domainEvent,
                            $domainMessage->getMetadata()
                        );
                        break;

                    case ImageUpdated::class:
                        $this->applyImageUpdated(
                            $domainEvent,
                            $domainMessage->getMetadata()
                        );
                        break;

                    case ImageDeleted::class:
                        $this->applyImageDeleted(
                            $domainEvent,
                            $domainMessage->getMetadata()
                        );
                        break;

                    default:
                        // Ignore any other actions
                }
            }
        }

        $this->decoratee->save($aggregate);
    }

    private function applyEventWasLabelled(
        EventWasLabelled $labelled,
        Metadata $metadata
    ) {
        $this->createImprovedEntryAPIFromMetadata($metadata)
            ->addKeyword(
                $labelled->getEventId(),
                $labelled->getLabel()
            );
    }

    private function applyUnlabelled(
        Unlabelled $unlabelled,
        Metadata $metadata
    ) {
        $this->createImprovedEntryAPIFromMetadata($metadata)
            ->deleteKeyword(
                $unlabelled->getEventId(),
                $unlabelled->getLabel()
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
        $this->setLocationForEvent($eventCreated->getLocation(), $event);
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

        $this->createImprovedEntryAPIFromMetadata($metadata)
            ->createEvent($event);

        return $eventCreated->getEventId();
    }

    /**
     * Listener on the EventDeleted event.
     * Also send a request to remove the event in UDB2.
     */
    public function applyEventDeleted(EventDeleted $eventDeleted, Metadata $metadata)
    {
        $entryApi = $this->createImprovedEntryAPIFromMetadata($metadata);
        return $entryApi->deleteEvent($eventDeleted->getEventId());
    }

    /**
     * Send the updated major info to UDB2.
     */
    public function applyMajorInfoUpdated(MajorInfoUpdated $infoUpdated, Metadata $metadata) {

        $entryApi = $this->createImprovedEntryAPIFromMetadata($metadata);
        $event = $entryApi->getEvent($infoUpdated->getEventId());

        $this->setLocationForEvent($infoUpdated->getLocation(), $event);
        $this->setCalendarForItemCreated($infoUpdated, $event);

        $detail = $event->getDetails()->getDetailByLanguage('nl');
        $detail->setTitle($infoUpdated->getTitle());

        // Set event type and theme.
        $newCategories = new CultureFeed_Cdb_Data_CategoryList();
        $categories = $event->getCategories();
        foreach ($categories as $category) {
          if ($category->getType() !== 'eventtype' && $category->getType() !== 'theme') {
            $newCategories->add($category);
          }
        }

        $eventType = new CultureFeed_Cdb_Data_Category(
            'eventtype',
            $infoUpdated->getEventType()->getId(),
            $infoUpdated->getEventType()->getLabel()
        );
        $newCategories->add($eventType);

        if ($infoUpdated->getTheme() !== null) {
            $theme = new CultureFeed_Cdb_Data_Category(
                'theme',
                $infoUpdated->getTheme()->getId(),
                $infoUpdated->getTheme()->getLabel()
            );
            $newCategories->add($theme);
        }
        $event->setCategories($newCategories);

        $entryApi->updateEvent($event);

    }

    /**
     * Send the updated description also to CDB2.
     */
    private function applyDescriptionUpdated(
        DescriptionUpdated $descriptionUpdated,
        Metadata $metadata
    ) {

        $entryApi = $this->createImprovedEntryAPIFromMetadata($metadata);

        $newDescription = $descriptionUpdated->getDescription();
        $entityId = $descriptionUpdated->getEventId();
        $entityType = new EntityType('event');
        $description = new String($newDescription);
        $language = new Language('nl');

        if (!empty($newDescription)) {
          $entryApi->updateDescription($entityId, $entityType, $description, $language);
        }
        else {
          $entryApi->deleteDescription($entityId, $entityType, $language);
        }

    }

    /**
     * Send the updated age range also to CDB2.
     */
    private function applyTypicalAgeRangeUpdated(
        TypicalAgeRangeUpdated $ageRangeUpdated,
        Metadata $metadata
    ) {

        $entryApi = $this->createImprovedEntryAPIFromMetadata($metadata);

        $ages = explode('-', $ageRangeUpdated->getTypicalAgeRange());
        $entityType = new EntityType('event');
        $age = new Number($ages[0]);
        $entryApi->updateAge($ageRangeUpdated->getEventId(), $entityType, $age);

    }

    /**
     * Send the deleted age range also to CDB2.
     */
    private function applyTypicalAgeRangeDeleted(
        TypicalAgeRangeDeleted $ageRangeDeleted,
        Metadata $metadata
    ) {

        $entryApi = $this->createImprovedEntryAPIFromMetadata($metadata);

        $entityType = new EntityType('event');
        $entryApi->deleteAge($ageRangeDeleted->getEventId(), $entityType);

    }

    /**
     * Apply the organizer updated event to the event repository.
     * @param OrganizerUpdated $organizerUpdated
     */
    private function applyOrganizerUpdated(
        OrganizerUpdated $organizerUpdated,
        Metadata $metadata
    ) {

        $organizerJSONLD = $this->organizerService->getEntity(
            $organizerUpdated->getOrganizerId()
        );

        $organizer = json_decode($organizerJSONLD);

        $entryApi = $this->createImprovedEntryAPIFromMetadata($metadata);

        $entityType = new EntityType('event');
        $organiserName = new String($organizer->name);

        $entryApi->updateOrganiser($organizerUpdated->getEventId(), $entityType, $organiserName);

    }

    /**
     * Delete the organizer also in UDB2..
     *
     * @param OrganizerDeleted $organizerDeleted
     * @param Metadata $metadata
     */
    private function applyOrganizerDeleted(
        OrganizerDeleted $organizerDeleted,
        Metadata $metadata
    ) {

        $entryApi = $this->createImprovedEntryAPIFromMetadata($metadata);
        $entityType = new EntityType('event');
        $entryApi->deleteOrganiser($organizerDeleted->getEventId(), $entityType);

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

        $this->updateCdbItemByContactPoint($event, $contactPoint);

        $entryApi->updateContactInfo(
            $domainEvent->getEventId(),
            new EntityType('event'),
            $event->getContactInfo()
        );

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

        $entityType = new EntityType('event');

        $this->updateCdbItemByBookingInfo($event, $bookingInfo);

        // Save contact info.
        $entryApi->updateContactInfo(
            $domainEvent->getEventId(),
            $entityType,
            $event->getContactInfo()
        );

        // Save the bookingperiod.
        if ($bookingInfo->getAvailabilityStarts() && $bookingInfo->getAvailabilityEnds()) {
          $startDate = new \DateTime($bookingInfo->getAvailabilityStarts());
          $endDate = new \DateTime($bookingInfo->getAvailabilityEnds());
          $bookingPeriod = new BookingPeriod(
              $startDate->format('d/m/Y'),
              $endDate->format('d/m/Y')
          );

          $entryApi->updateBookingPeriod(
              $domainEvent->getEventId(),
              $bookingPeriod
          );
        }

    }

    /**
     * Apply the imageAdded event to udb2.
     * @param ImageAdded $domainEvent
     */
    private function applyImageAdded(
        ImageAdded $domainEvent,
        Metadata $metadata
    ) {

        $entryApi = $this->createImprovedEntryAPIFromMetadata($metadata);
        $event = $entryApi->getEvent($domainEvent->getEventId());

        $this->addImageToCdbItem($event, $domainEvent->getMediaObject());
        $entryApi->updateEvent($event);

    }

    /**
     * Apply the imageUpdated event to udb2.
     * @param ImageAdded $domainEvent
     */
    private function applyImageUpdated(
        ImageUpdated $domainEvent,
        Metadata $metadata
    ) {

        $entryApi = $this->createImprovedEntryAPIFromMetadata($metadata);
        $event = $entryApi->getEvent($domainEvent->getEventId());

        $this->updateImageOnCdbItem($event, $domainEvent->getIndexToUpdate(), $domainEvent->getMediaObject());
        $entryApi->updateEvent($event);

    }

    /**
     * Apply the imageDeleted event to udb2.
     * @param ImageDeleted $domainEvent
     */
    private function applyImageDeleted(
        ImageDeleted $domainEvent,
        Metadata $metadata
    ) {

        $entryApi = $this->createImprovedEntryAPIFromMetadata($metadata);
        $event = $entryApi->getEvent($domainEvent->getEventId());

        $this->deleteImageOnCdbItem($event, $domainEvent->getIndexToDelete());
        $entryApi->updateEvent($event);

    }

    /**
     * Set the location on the cdb event based on an eventCreated event.
     *
     * @param Location $location
     * @param CultureFeed_Cdb_Item_Event $cdbEvent
     */
    private function setLocationForEvent(Location $location, CultureFeed_Cdb_Item_Event $cdbEvent)
    {

        $placeEntity = $this->placeService->getEntity($location->getCdbid());
        $place = json_decode($placeEntity);

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
            $physicalAddress->setStreet($location->getStreet());
        }

        $address = new CultureFeed_Cdb_Data_Address($physicalAddress);

        $cdbLocation = new CultureFeed_Cdb_Data_Location($address);
        $cdbLocation->setLabel($location->getName());
        $cdbEvent->setLocation($cdbLocation);

    }
}
