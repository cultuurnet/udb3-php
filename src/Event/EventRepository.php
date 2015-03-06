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
 *
 * This class used to extend EventSourcingRepository from the Broadway library,
 * however we had to change it to publish the decorated event stream to the
 * event bus, instead of the non-decorated event stream. See the add() method.
 *
 * @link https://github.com/qandidate-labs/broadway/issues/61
 */
class EventRepository implements RepositoryInterface
{
    use \CultuurNet\UDB3\Udb3RepositoryTrait;

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
}
