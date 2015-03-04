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
use CultuurNet\Entry\EntryAPI;
use CultuurNet\Search\Parameter\Query;
use CultuurNet\UDB3\Cdb\EventItemFactory;
use CultuurNet\UDB3\EntityNotFoundException;
use CultuurNet\UDB3\Event\DescriptionTranslated;
use CultuurNet\UDB3\Event\Event;
use CultuurNet\UDB3\Event\EventCreated;
use CultuurNet\UDB3\Event\EventWasTagged;
use CultuurNet\UDB3\Event\TagErased;
use CultuurNet\UDB3\Event\TitleTranslated;
use CultuurNet\UDB3\Event\TypicalAgeRangeUpdated;
use CultuurNet\UDB3\Event\DescriptionUpdated;
use CultuurNet\UDB3\OrganizerService;
use CultuurNet\UDB3\PlaceService;
use CultuurNet\UDB3\SearchAPI2\SearchServiceInterface;
use RuntimeException;
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
     * @var PlaceService
     */
    protected $placeService;

    /**
     * @var EventStreamDecoratorInterface[]
     */
    private $eventStreamDecorators = array();

    public function __construct(
        RepositoryInterface $decoratee,
        SearchServiceInterface $search,
        EntryAPIImprovedFactory $entryAPIImprovedFactory,
        PlaceService $placeService,
        OrganizerService $organizerService,
        array $eventStreamDecorators = array()
    ) {
        $this->decoratee = $decoratee;
        $this->search = $search;
        $this->entryAPIImprovedFactory = $entryAPIImprovedFactory;
        $this->eventStreamDecorators = $eventStreamDecorators;
        $this->organizerService = $organizerService;
        $this->placeService = $placeService;
    }

    public function syncBackOn()
    {
        $this->syncBack = true;
    }

    public function syncBackOff()
    {
        $this->syncBack = false;
    }

    private function getType()
    {
        return Event::class;
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

                    case 'CultuurNet\\UDB3\\Event\\DescriptionUpdated':
                        /** @var DescriptionUpdated $domainEvent */
                        $this->applyDescriptionUpdated(
                            $domainEvent,
                            $domainMessage->getMetadata()
                        );
                        break;

                    case EventCreated::class:
                        $this->applyEventCreated($domainEvent, $domainMessage->getMetadata());
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

    /**
     * Send the updated description also to CDB2.
     */
    private function applyDescriptionUpdated(
        DescriptionUpdated $descriptionUpdated,
        Metadata $metadata
    ) {

        $entryApi = $this->createImprovedEntryAPIFromMetadata($metadata);
        $event = $entryApi->getEvent($descriptionUpdated->getEventId());

        $event->getDetails()->getDetailByLanguage('nl')->setLongDescription($domainEven>getDescription());

        $entryApi->updateEvent($event->getCdbId(), $event);

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

        $entryApi->updateEvent($event->getCdbId(), $event);

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
            // @todo Use Entry API instead of search service.
            $results = $this->search->search(
                [new Query('cdbid:' . $id)]
            );

            $cdbXml = $results->getBody(true);

            $reader = new \XMLReader();

            $reader->xml($cdbXml);

            while ($reader->read()) {
                switch ($reader->nodeType) {
                    case ($reader::ELEMENT):
                        if ($reader->localName == "event" &&
                            $reader->getAttribute('cdbid') == $id
                        ) {
                            $node = $reader->expand();
                            $dom = new \DomDocument('1.0');
                            $n = $dom->importNode($node, true);
                            $dom->appendChild($n);
                            $eventXml = $dom->saveXML();
                        }
                }
            }

            if (!isset($eventXml)) {
                throw AggregateNotFoundException::create($id);
            }

            $udb2Event = EventItemFactory::createEventFromCdbXml(
                \CultureFeed_Cdb_Default::CDB_SCHEME_URL,
                $eventXml
            );
            $this->importDependencies($udb2Event);

            $event = Event::importFromUDB2(
                $id,
                $eventXml,
                \CultureFeed_Cdb_Default::CDB_SCHEME_URL
            );

            $this->add($event);
        }

        return $event;
    }

    private function importDependencies(\CultureFeed_Cdb_Item_Event $udb2Event)
    {
        try {
            $location = $udb2Event->getLocation();
            if ($location && $location->getCdbid()) {
                // Loading the place will implicitly import it, or throw an error
                // if the place is not known.
                $this->placeService->getEntity($location->getCdbid());
            }
        } catch (EntityNotFoundException $e) {
            if ($this->logger) {
                $this->logger->error(
                    "Unable to retrieve location with ID {$location->getCdbid(
                    )}, of event {$udb2Event->getCdbId()}."
                );
            } else {
                throw $e;
            }
        }

        try {
            $organizer = $udb2Event->getOrganiser();
            if ($organizer && $organizer->getCdbid()) {
                // Loading the organizer will implicitly import it, or throw an error
                // if the organizer is not known.
                $this->organizerService->getEntity($organizer->getCdbid());
            }
        } catch (EntityNotFoundException $e) {
            if ($this->logger) {
                $this->logger->error(
                    "Unable to retrieve organizer with ID {$organizer->getCdbid(
                    )}, of event {$udb2Event->getCdbId()}."
                );
            } else {
                throw $e;
            }
        }
    }

    /**
     * Listener on the eventCreated event. Send a new event also to UDB2.
     */
    public function applyEventCreated(EventCreated $eventCreated, Metadata $metadata)
    {

        $event = new \CultureFeed_Cdb_Item_Event();

        // This currently does not work when POSTed to the entry API
        $event->setCdbId($eventCreated->getEventId());

        $nlDetail = new \CultureFeed_Cdb_Data_EventDetail();
        $nlDetail->setLanguage('nl');
        $nlDetail->setTitle($eventCreated->getTitle());

        $details = new \CultureFeed_Cdb_Data_EventDetailList();
        $details->add($nlDetail);
        $event->setDetails($details);

        // Set location and calendar info.
        $this->setLocationForEventCreated($eventCreated, $event);
        $this->setCalendarForItemCreated($eventCreated, $event);

        // Set event type and theme.
        $event->setCategories(new \CultureFeed_Cdb_Data_CategoryList());
        $eventType = new \CultureFeed_Cdb_Data_Category(
            'eventtype',
            $eventCreated->getEventType()->getId(),
            $eventCreated->getEventType()->getLabel()
        );
        $event->getCategories()->add($eventType);

        if ($eventCreated->getTheme() !== null) {
            $theme = new \CultureFeed_Cdb_Data_Category(
                'theme',
                $eventCreated->getTheme()->getId(),
                $eventCreated->getTheme()->getLabel()
            );
            $event->getCategories()->add($theme);
        }

        // Empty contact info.
        $contactInfo = new \CultureFeed_Cdb_Data_ContactInfo();
        $event->setContactInfo($contactInfo);

        $cdbXml = new \CultureFeed_Cdb_Default();
        $cdbXml->addItem($event);

        $this->createImprovedEntryAPIFromMetadata($metadata)
            ->createEvent((string)$cdbXml);

        return $eventCreated->getEventId();
    }

    /**
     * Set the location on the cdb event based on an eventCreated event.
     *
     * @param EventCreated $eventCreated
     * @param \CultureFeed_Cdb_Item_Event $cdbEvent
     */
    private function setLocationForEventCreated(EventCreated $eventCreated, \CultureFeed_Cdb_Item_Event $cdbEvent)
    {

        $placeEntity = $this->placeService->getEntity($eventCreated->getLocation()->getCdbid());
        $place = json_decode($placeEntity);

        $eventLocation = $eventCreated->getLocation();

        $physicalAddress = new \CultureFeed_Cdb_Data_Address_PhysicalAddress();
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

        $address = new \CultureFeed_Cdb_Data_Address($physicalAddress);

        $location = new \CultureFeed_Cdb_Data_Location($address);
        $location->setLabel($eventLocation->getName());
        $cdbEvent->setLocation($location);

    }
}
