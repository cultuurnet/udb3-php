<?php

/**
 * @file
 * Contains \Cultuurnet\UDB3\UDB2\PlaceRepository.
 */

namespace CultuurNet\UDB3\UDB2;

use Broadway\Domain\AggregateRoot;
use Broadway\Domain\DomainEventStream;
use Broadway\Domain\DomainMessageInterface;
use Broadway\Domain\Metadata;
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
use CultuurNet\UDB3\Actor\ActorImportedFromUDB2;
use CultuurNet\UDB3\OrganizerService;
use CultuurNet\UDB3\Place\DescriptionUpdated;
use CultuurNet\UDB3\Place\Place;
use CultuurNet\UDB3\Place\PlaceCreated;
use CultuurNet\UDB3\SearchAPI2\SearchServiceInterface;
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

    /**
     * @var boolean
     */
    protected $syncBack = false;

    /**
     * @var EntryAPIImprovedFactory
     */
    protected $entryAPIImprovedFactory;

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

                    case DescriptionUpdated::class:
                        /** @var DescriptionUpdated $domainEvent */
                        $this->applyDescriptionUpdated(
                            $domainEvent,
                            $domainMessage->getMetadata()
                        );
                        break;

                    case PlaceCreated::class:
                        $this->applyPlaceCreated($domainEvent, $domainMessage->getMetadata());
                        break;

                    default:
                        // Ignore any other actions
                }
            }
        }

        $this->decoratee->add($aggregate);
    }

    /**
     * Returns the type.
     * @return string
     */
    protected function getType()
    {
        return Place::class;
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

        // This currently does not work when POSTed to the entry API
        $event->setCdbId($placeCreated->getPlaceId());

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
     * Set the location on the cdbEvent based on a PlaceCreated event.
     */
    private function setLocationForPlaceCreated(PlaceCreated $placeCreated, CultureFeed_Cdb_Item_Event $cdbEvent)
    {

        $address = $placeCreated->getAddress();

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

        $cdbAddress = new CultureFeed_Cdb_Data_Address($physicalAddress);

        $location = new CultureFeed_Cdb_Data_Location($cdbAddress);
        $location->setLabel($placeCreated->getTitle());
        $cdbEvent->setLocation($location);

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
}
