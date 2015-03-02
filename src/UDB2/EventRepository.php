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
use CultuurNet\UDB3\OrganizerService;
use CultuurNet\UDB3\PlaceService;
use CultuurNet\UDB3\SearchAPI2\SearchServiceInterface;
use CultuurNet\UDB3\Timestamps;
use CultuurNet\UDB3\Event\TypicalAgeRangeUpdated;
use CultuurNet\UDB3\Event\DescriptionUpdated;
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
        return '\\CultuurNet\\UDB3\\Event\\Event';
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
                    case 'CultuurNet\\UDB3\\Event\\EventWasTagged':
                        /** @var EventWasTagged $domainEvent */
                        $this->applyEventWasTagged(
                            $domainEvent,
                            $domainMessage->getMetadata()
                        );
                        break;

                    case 'CultuurNet\\UDB3\\Event\\TagErased':
                        /** @var TagErased $domainEvent */
                        $this->applyTagErased(
                            $domainEvent,
                            $domainMessage->getMetadata()
                        );

                        break;

                    case 'CultuurNet\\UDB3\\Event\\TitleTranslated':
                        /** @var TitleTranslated $domainEvent */
                        $this->applyTitleTranslated(
                            $domainEvent,
                            $domainMessage->getMetadata()
                        );
                        break;

                    case 'CultuurNet\\UDB3\\Event\\DescriptionTranslated':
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
        DescriptionUpdated $domainEvent,
        Metadata $metadata
    ) {

        $entryApi = $this->createImprovedEntryAPIFromMetadata($metadata);
        $event = $entryApi->getEvent($domainEvent->getEventId());

        $event->getDetails()->getDetailByLanguage('nl')->setLongDescription($domainEven>getDescription());

        $entryApi->updateEvent($event->getCdbId(), $event);

    }

    /**
     * Send the updated description also to CDB2.
     */
    private function applyTypicalAgeRangeUpdated(
        TypicalAgeRangeUpdated $domainEvent,
        Metadata $metadata
    ) {

        $entryApi = $this->createImprovedEntryAPIFromMetadata($metadata);
        $event = $entryApi->getEvent($domainEvent->getEventId());

        $ages = explode('-', $domainEvent->getTypicalAgeRange());
        $event->setAgeFrom($ages[0]);

        $entryApi->updateEvent($event->getCdbId(), $event);

    }

    /**
     * @param Metadata $metadata
     * @return EntryAPI
     */
    private function createImprovedEntryAPIFromMetadata(Metadata $metadata)
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

    public function applyEventCreated(EventCreated $eventCreated, Metadata $metadata)
    {

        // Don't send to UDB2 for now.
        return $eventCreated->getEventId();

        $event = new \CultureFeed_Cdb_Item_Event();

        // This currently does not work when POSTed to the entry API
        $event->setCdbId($eventCreated->getEventId());

        $nlDetail = new \CultureFeed_Cdb_Data_EventDetail();
        $nlDetail->setLanguage('nl');
        $nlDetail->setTitle($eventCreated->getTitle());

        $details = new \CultureFeed_Cdb_Data_EventDetailList();
        $details->add($nlDetail);

        // We need to retrieve the real place in order to
        // pass on its address to UDB2.
        //$place = $this->placeService->getEntity($eventCreated->getLocation());
        //$place = json_decode($place);

        $eventLocation = $eventCreated->getLocation();

        $physicalAddress = new \CultureFeed_Cdb_Data_Address_PhysicalAddress();
        $physicalAddress->setCountry($eventLocation->getCountry());
        $physicalAddress->setCity($eventLocation->getLocality());
        $physicalAddress->setZip($eventLocation->getPostalcode());
        // @todo This is not an exact mapping, because we do not have a separate
        // house number in JSONLD, this should be fixed somehow. Probably it's
        // better to use another read model than JSON-LD for this purpose.
        $physicalAddress->setStreet($eventLocation->getStreet());
        $address = new \CultureFeed_Cdb_Data_Address($physicalAddress);

        $location = new \CultureFeed_Cdb_Data_Location($address);
        $location->setLabel($eventLocation->getName());
//        $location->setCdbid($eventCreated->getLocation());

        $event->setLocation($location);

        $event->setCategories(new \CultureFeed_Cdb_Data_CategoryList());

        $eventType = new \CultureFeed_Cdb_Data_Category(
          'eventtype',
          $eventCreated->getEventType()->getId(),
          $eventCreated->getEventType()->getLabel()
        );
        $event->getCategories()->add($eventType);

        $calendar = new \CultureFeed_Cdb_Data_Calendar_TimestampList();
        $eventCalendar = $eventCreated->getCalendar();
        if ($eventCalendar->getType() == Timestamps::TYPE) {

          foreach ($eventCalendar->getTimestamps() as $timestamp) {
            $calendar->add(
                new \CultureFeed_Cdb_Data_Calendar_Timestamp(
                    $timestamp->getDate(),
                    $timestamp->getTimestart(),
                    $timestamp->getTimeend()
                )
            );
          }

        }

        $event->setCalendar($calendar);

        $event->setDetails($details);

        $contactInfo = new \CultureFeed_Cdb_Data_ContactInfo();
        $event->setContactInfo($contactInfo);

        $cdbXml = new \CultureFeed_Cdb_Default();
        $cdbXml->addItem($event);

        $this->createImprovedEntryAPIFromMetadata($metadata)
            ->createEvent((string)$cdbXml);

        return $eventCreated->getEventId();
    }
}
