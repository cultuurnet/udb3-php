<?php

/**
 * @file
 * Contains \Cultuurnet\UDB3\Place\PlaceRepository
 */

namespace CultuurNet\UDB3\Place;

use Broadway\EventHandling\EventBusInterface;
use Broadway\EventSourcing\EventSourcingRepository;
use Broadway\EventSourcing\EventStreamDecoratorInterface;
use Broadway\EventStore\EventStoreInterface;

class PlaceRepository extends EventSourcingRepository
{
    use \CultuurNet\UDB3\Udb3RepositoryTrait;

    private $eventStore;
    private $eventBus;
    private $aggregateClass;
    private $eventStreamDecorators = array();

    /**
     * @param EventStoreInterface $eventStore
     * @param EventBusInterface $eventBus
     * @param EventStreamDecoratorInterface[] $eventStreamDecorators
     */
    public function __construct(
        EventStoreInterface $eventStore,
        EventBusInterface $eventBus,
        array $eventStreamDecorators = array()
    ) {
        $this->eventStore            = $eventStore;
        $this->eventBus              = $eventBus;
        $this->aggregateClass        = Place::class;
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
