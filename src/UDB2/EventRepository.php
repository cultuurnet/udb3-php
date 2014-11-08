<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\UDB2;


use Broadway\Domain\AggregateRoot;
use Broadway\Domain\DomainEventStream;
use Broadway\Domain\DomainMessageInterface;
use Broadway\EventSourcing\EventStreamDecoratorInterface;
use Broadway\Repository\AggregateNotFoundException;
use Broadway\Repository\RepositoryInterface;
use CultuurNet\Auth\TokenCredentials;
use CultuurNet\Search\Parameter\Query;
use CultuurNet\UDB3\Event\Event;
use CultuurNet\UDB3\Event\EventWasTagged;
use CultuurNet\UDB3\SearchAPI2\SearchServiceInterface;

/**
 * Repository decorator that first updates UDB2.
 *
 * When a failure on UDB2 occurs, the whole transaction will fail.
 */
class EventRepository implements RepositoryInterface
{
    /**
     * @var RepositoryInterface
     */
    protected $decoratee;

    /**
     * @var SearchServiceInterface
     */
    protected $search;

    /**
     * @var EntryAPIFactory
     */
    protected $entryAPIFactory;

    /**
     * @var EventStreamDecoratorInterface[]
     */
    private $eventStreamDecorators = array();

    public function __construct(
        RepositoryInterface $decoratee,
        SearchServiceInterface $search,
        EntryAPIFactory $entryAPIFactory,
        array $eventStreamDecorators = array()
    ) {
        $this->decoratee = $decoratee;
        $this->search = $search;
        $this->entryAPIFactory = $entryAPIFactory;
        $this->eventStreamDecorators = $eventStreamDecorators;
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
        $domainEventStream = $aggregate->getUncommittedEvents();
        $eventStream = $this->decorateForWrite($aggregate, $domainEventStream);

        /** @var DomainMessageInterface $domainMessage */
        foreach ($eventStream as $domainMessage) {
            $domainEvent = $domainMessage->getPayload();
            switch (get_class($domainEvent)) {
                case 'CultuurNet\\UDB3\\Event\\EventWasTagged':
                    /** @var EventWasTagged $domainEvent */
                    $event = new \CultureFeed_Cdb_Item_Event();
                    $event->setCdbId($domainEvent->getEventId());
                    // At this point we need to have
                    // - the user associated with the event, from the metadata
                    // - the token and secret of the user stored in the database
                    $token = '';
                    $secret = '';
                    $entryAPI = $this->entryAPIFactory->withTokenCredentials(
                        new TokenCredentials(
                            $token,
                            $secret
                        )
                    );
                    $entryAPI->addTagToEvent(
                        $event,
                        [$domainEvent->getKeyword()]
                    );
                    break;
                default:
                    // Ignore any other actions
            }
        }

        $this->decoratee->add($aggregate);
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

            $event = Event::importFromUDB2($id, $eventXml);
        }

        return $event;
    }
}
