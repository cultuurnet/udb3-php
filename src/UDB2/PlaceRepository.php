<?php

/**
 * @file
 * Contains \Cultuurnet\UDB3\UDB2\PlaceRepository.
 */

namespace CultuurNet\UDB3\UDB2;

use Broadway\Domain\AggregateRoot;
use Broadway\Domain\DomainMessage;
use Broadway\Domain\Metadata;
use Broadway\EventSourcing\EventStreamDecoratorInterface;
use Broadway\Repository\RepositoryInterface;
use CultureFeed_Cdb_Data_Address;
use CultureFeed_Cdb_Data_Category;
use CultureFeed_Cdb_Data_CategoryList;
use CultureFeed_Cdb_Data_ContactInfo;
use CultureFeed_Cdb_Data_EventDetail;
use CultureFeed_Cdb_Data_EventDetailList;
use CultureFeed_Cdb_Data_Location;
use CultureFeed_Cdb_Data_Organiser;
use CultureFeed_Cdb_Default;
use CultureFeed_Cdb_Item_Event;
use CultuurNet\UDB3\Actor\ActorImportedFromUDB2;
use CultuurNet\UDB3\Facility;
use CultuurNet\UDB3\OrganizerService;
use CultuurNet\UDB3\Place\Events\BookingInfoUpdated;
use CultuurNet\UDB3\Place\Events\ContactPointUpdated;
use CultuurNet\UDB3\Place\Events\DescriptionUpdated;
use CultuurNet\UDB3\Place\Events\FacilitiesUpdated;
use CultuurNet\UDB3\Place\Events\ImageAdded;
use CultuurNet\UDB3\Place\Events\ImageDeleted;
use CultuurNet\UDB3\Place\Events\ImageUpdated;
use CultuurNet\UDB3\Place\Events\OrganizerDeleted;
use CultuurNet\UDB3\Place\Events\OrganizerUpdated;
use CultuurNet\UDB3\Place\Events\PlaceCreated;
use CultuurNet\UDB3\Place\Events\PlaceDeleted;
use CultuurNet\UDB3\Place\Events\TypicalAgeRangeUpdated;
use CultuurNet\UDB3\Place\Place;
use CultuurNet\UDB3\SearchAPI2\SearchServiceInterface;
use CultuurNet\UDB3\Udb3RepositoryTrait;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

/**
 * Repository decorator that first updates UDB2.
 *
 * When a failure on UDB2 occurs, the whole transaction will fail.
 */
class PlaceRepository extends ActorRepository implements RepositoryInterface, LoggerAwareInterface
{

    use LoggerAwareTrait;
    use Udb2UtilityTrait;
    use Udb3RepositoryTrait;

    /**
     * @var RepositoryInterface
     */
    protected $decoratee;

    /**
     * @var SearchServiceInterface
     */
    protected $search;

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
     * @var EventStreamDecoratorInterface[]
     */
    private $eventStreamDecorators = array();

    private $aggregateClass;

    public function __construct(
        RepositoryInterface $decoratee,
        SearchServiceInterface $search,
        EntryAPIImprovedFactory $entryAPIImprovedFactory,
        OrganizerService $organizerService,
        array $eventStreamDecorators = array()
    ) {
        $this->decoratee = $decoratee;
        $this->search = $search;
        $this->entryAPIImprovedFactory = $entryAPIImprovedFactory;
        $this->eventStreamDecorators = $eventStreamDecorators;
        $this->organizerService = $organizerService;
        $this->aggregateClass = Place::class;
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

                    case PlaceCreated::class:
                        $this->applyPlaceCreated(
                          $domainEvent,
                          $domainMessage->getMetadata()
                        );
                        break;

                    case PlaceDeleted::class:
                        $this->applyPlaceDeleted(
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

                    case FacilitiesUpdated::class:
                        $this->applyFacilitiesUpdated(
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

    /**
     * Imports from UDB2.
     *
     * @param string $id
     *   The id.
     * @param string $actorXml
     *   The actor xml.
     * @param string $cdbSchemeUrl
     *
     * @return ActorImportedFromUDB2
     */
    protected function importFromUDB2($id, $actorXml, $cdbSchemeUrl)
    {
        return Place::importFromUDB2(
            $id,
            $actorXml,
            $cdbSchemeUrl
        );
    }

    /**
     * Listener on the placeCreated event. Send a new place also to UDB2 as event.
     */
    public function applyPlaceCreated(PlaceCreated $placeCreated, Metadata $metadata)
    {

        $event = new CultureFeed_Cdb_Item_Event();
        $event->setCdbId($placeCreated->getPlaceId());
        $event->addKeyword('UDB3 place');

        $nlDetail = new CultureFeed_Cdb_Data_EventDetail();
        $nlDetail->setLanguage('nl');
        $nlDetail->setTitle($placeCreated->getTitle());

        $details = new CultureFeed_Cdb_Data_EventDetailList();
        $details->add($nlDetail);
        $event->setDetails($details);

        // Set location and calendar info.
        $this->setLocationForPlaceCreated($placeCreated, $event);
        $this->setCalendarForItemCreated($placeCreated, $event);

        // Set event type and theme.
        $event->setCategories(new CultureFeed_Cdb_Data_CategoryList());
        $eventType = new CultureFeed_Cdb_Data_Category(
            'eventtype',
            $placeCreated->getEventType()->getId(),
            $placeCreated->getEventType()->getLabel()
        );
        $event->getCategories()->add($eventType);

        if ($placeCreated->getTheme() !== null) {
            $theme = new CultureFeed_Cdb_Data_Category(
                'theme',
                $placeCreated->getTheme()->getId(),
                $placeCreated->getTheme()->getLabel()
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

        return $placeCreated->getPlaceId();
    }

    /**
     * Listener on the placeDeleted event.
     * Also send a request to remove the place in UDB2.
     */
    public function applyPlaceDeleted(PlaceDeleted $placeDeleted, Metadata $metadata)
    {
        $entryApi = $this->createImprovedEntryAPIFromMetadata($metadata);
        return $entryApi->deleteEvent($placeDeleted->getPlaceId());
    }

    /**
     * Set the location on the cdbEvent based on a PlaceCreated event.
     */
    private function setLocationForPlaceCreated(PlaceCreated $placeCreated, CultureFeed_Cdb_Item_Event $cdbEvent)
    {

        $address = $placeCreated->getAddress();
        $cdbAddress = new CultureFeed_Cdb_Data_Address($this->getPhysicalAddressForUdb3Address($address));

        $location = new CultureFeed_Cdb_Data_Location($cdbAddress);
        $location->setLabel($placeCreated->getTitle());
        $cdbEvent->setLocation($location);

    }

    /**
     * Send the updated description also to CDB2.
     */
    private function applyDescriptionUpdated(
        DescriptionUpdated $descriptionUpdated,
        Metadata $metadata
    ) {

        $entryApi = $this->createImprovedEntryAPIFromMetadata($metadata);
        $event = $entryApi->getEvent($descriptionUpdated->getPlaceId());

        $event->getDetails()->getDetailByLanguage('nl')->setLongDescription($descriptionUpdated->getDescription());

        $entryApi->updateEvent($event);

    }

    /**
     * Send the updated age range also to CDB2.
     */
    private function applyTypicalAgeRangeUpdated(
        TypicalAgeRangeUpdated $domainEvent,
        Metadata $metadata
    ) {

        $entryApi = $this->createImprovedEntryAPIFromMetadata($metadata);
        $event = $entryApi->getEvent($domainEvent->getPlaceId());

        $ages = explode('-', $domainEvent->getTypicalAgeRange());
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
        $event = $entryApi->getEvent($domainEvent->getPlaceId());

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
        $event = $entryApi->getEvent($domainEvent->getPlaceId());
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
        $event = $entryApi->getEvent($domainEvent->getPlaceId());
        $contactPoint = $domainEvent->getContactPoint();

        $this->updateCdbItemByContactPoint($event, $contactPoint);
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
        $event = $entryApi->getEvent($domainEvent->getPlaceId());
        $bookingInfo = $domainEvent->getBookingInfo();

        $this->updateCdbItemByBookingInfo($event, $bookingInfo);

        $entryApi->updateEvent($event);

    }

    /**
     * Apply the facilitiesupdated event to udb2.
     * @param FacilitiesUpdated $facilitiesUpdated
     */
    private function applyFacilitiesUpdated(
        FacilitiesUpdated $domainEvent,
        Metadata $metadata
    ) {

        $entryApi = $this->createImprovedEntryAPIFromMetadata($metadata);
        $event = $entryApi->getEvent($domainEvent->getPlaceId());

        $cdbCategories = $event->getCategories();

        // Remove all old facilities.
        foreach ($cdbCategories as $key => $category) {
            if ($category->getType() == Facility::DOMAIN) {
                $cdbCategories->delete($key);
            }
        }

        // Add the new facilities.
        foreach ($domainEvent->getFacilities() as $facility) {
            $cdbCategories->add(new \CultureFeed_Cdb_Data_Category(Facility::DOMAIN, $facility->getId(), $facility->getLabel()));
        }

        $entryApi->updateEvent($event);

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
        $event = $entryApi->getEvent($domainEvent->getPlaceId());

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
        $event = $entryApi->getEvent($domainEvent->getPlaceId());

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
        $event = $entryApi->getEvent($domainEvent->getPlaceId());

        $this->deleteImageOnCdbItem($event, $domainEvent->getIndexToDelete());
        $entryApi->updateEvent($event);

    }
}
