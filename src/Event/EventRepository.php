<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Event;

use Assert\Assertion;
use Broadway\Domain\AggregateRoot;
use Broadway\Domain\DomainEventStream;
use Broadway\EventHandling\EventBusInterface;
use Broadway\EventSourcing\EventStreamDecoratorInterface;
use Broadway\EventStore\EventStoreInterface;
use Broadway\EventStore\EventStreamNotFoundException;
use Broadway\Repository\AggregateNotFoundException;
use Broadway\Repository\RepositoryInterface;

/**
 * Class EventRepository
 * @package CultuurNet\UDB3\Event
 *
 * This class used to extend EventSourcingRepository from the Broadway library,
 * however we had to change it to publish the decorated event stream to the
 * event bus, instead of the non-decorated event stream. See the add() method.
 *
 * @see https://github.com/qandidate-labs/broadway/issues/61
 */
class EventRepository implements RepositoryInterface
{
    private $eventStore;
    private $eventBus;
    private $aggregateClass;
    private $eventStreamDecorators = array();

    /**
     * @param EventStoreInterface             $eventStore
     * @param EventBusInterface               $eventBus
     * @param EventStreamDecoratorInterface[] $eventStreamDecorators
     */
    public function __construct(
      EventStoreInterface $eventStore,
      EventBusInterface $eventBus,
      array $eventStreamDecorators = array()
    ) {
        $this->eventStore            = $eventStore;
        $this->eventBus              = $eventBus;
        $this->aggregateClass        = Event::class;
        $this->eventStreamDecorators = $eventStreamDecorators;
    }

    /**
     * {@inheritDoc}
     */
    public function load($id)
    {
        try {
            $domainEventStream = $this->eventStore->load($id);

            $aggregate = new $this->aggregateClass();
            $aggregate->initializeState($domainEventStream);

            return $aggregate;
        } catch (EventStreamNotFoundException $e) {
            throw AggregateNotFoundException::create($id, $e);
        }
    }

    /**
     * {@inheritDoc}
     *
     * Overwritten
     */
    public function add(AggregateRoot $aggregate)
    {
        Assertion::isInstanceOf($aggregate, $this->getType());

        $domainEventStream = $aggregate->getUncommittedEvents();
        $eventStream       = $this->decorateForWrite($aggregate, $domainEventStream);
        $this->eventStore->append($aggregate->getAggregateRootId(), $eventStream);
        $this->eventBus->publish($eventStream);
    }

    private function decorateForWrite(AggregateRoot $aggregate, DomainEventStream $eventStream)
    {
        $aggregateType       = $this->getType();
        $aggregateIdentifier = $aggregate->getAggregateRootId();

        foreach ($this->eventStreamDecorators as $eventStreamDecorator) {
            $eventStream = $eventStreamDecorator->decorateForWrite($aggregateType, $aggregateIdentifier, $eventStream);
        }

        return $eventStream;
    }

    private function getType()
    {
        return $this->aggregateClass;
    }
}
