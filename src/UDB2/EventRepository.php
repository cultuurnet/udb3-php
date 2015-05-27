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
use CultuurNet\Entry\EntryAPI;
use CultuurNet\UDB3\Event\DescriptionTranslated;
use CultuurNet\UDB3\Event\Event;
use CultuurNet\UDB3\Event\EventCreated;
use CultuurNet\UDB3\Event\Events\EventWasLabelled;
use CultuurNet\UDB3\Event\Events\Unlabelled;
use CultuurNet\UDB3\Event\TitleTranslated;
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
     * @var EntryAPIImprovedFactory
     */
    protected $entryAPIImprovedFactory;

    /**
     * @var boolean
     */
    protected $syncBack = false;

    /**
     * @var EventStreamDecoratorInterface[]
     */
    private $eventStreamDecorators = array();

    /**
     * @var EventImporterInterface
     */
    protected $eventImporter;

    public function __construct(
        RepositoryInterface $decoratee,
        EntryAPIImprovedFactory $entryAPIImprovedFactory,
        EventImporterInterface $eventImporter,
        array $eventStreamDecorators = array()
    ) {
        $this->decoratee = $decoratee;
        $this->entryAPIImprovedFactory = $entryAPIImprovedFactory;
        $this->eventStreamDecorators = $eventStreamDecorators;
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

    private function getType()
    {
        return Event::class;
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
                        /** @var \CultuurNet\UDB3\Event\Events\Unlabelled $domainEvent */
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
                        $this->applyEventCreated($domainEvent, $domainMessage->getMetadata());
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

    /**
     * @param Metadata $metadata
     * @return EntryAPI
     */
    private function createImprovedEntryAPIFromMetadata(Metadata $metadata)
    {
        $metadata = $metadata->serialize();
        if (!isset($metadata['uitid_token_credentials'])) {
            throw new \RuntimeException('No token credentials found. They are needed to access the entry API, so aborting request.');
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
            $event = $this->eventImporter->createEventFromUDB2($id);

            if (!$event) {
                throw new AggregateNotFoundException($id);
            }
        }

        return $event;
    }

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

        // We need to retrieve the real place in order to
        // pass on its address to UDB2.
        $place = $this->placeService->getEntity($eventCreated->getLocation());
        $place = json_decode($place);

        $physicalAddress = new \CultureFeed_Cdb_Data_Address_PhysicalAddress();
        $physicalAddress->setCountry($place->address->addressCountry);
        $physicalAddress->setCity($place->address->addressLocality);
        $physicalAddress->setZip($place->address->postalCode);
        // @todo This is not an exact mapping, because we do not have a separate
        // house number in JSONLD, this should be fixed somehow. Probably it's
        // better to use another read model than JSON-LD for this purpose.
        $physicalAddress->setStreet($place->address->streetAddress);
        $address = new \CultureFeed_Cdb_Data_Address($physicalAddress);

        $location = new \CultureFeed_Cdb_Data_Location($address);
        $location->setLabel($place->name);
        $location->setCdbid($eventCreated->getLocation());

        $event->setLocation($location);

        $event->setCategories(new \CultureFeed_Cdb_Data_CategoryList());
        $concertCategory = new \CultureFeed_Cdb_Data_Category(
            'eventtype',
            $eventCreated->getType()->getId(),
            $eventCreated->getType()->getLabel()
        );
        $event->getCategories()->add($concertCategory);

        $calendar = new \CultureFeed_Cdb_Data_Calendar_TimestampList();
        $calendar->add(
            new \CultureFeed_Cdb_Data_Calendar_Timestamp(
                $eventCreated->getDate()->format('Y-m-d'),
                $eventCreated->getDate()->format('H:i:s.u')
            )
        );
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
