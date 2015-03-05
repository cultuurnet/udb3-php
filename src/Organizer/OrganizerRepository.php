<?php

/**
 * @file
 * Contains \Cultuurnet\UDB3\Organizer\OrganizerRepository
 */

namespace CultuurNet\UDB3\Organizer;

use Broadway\EventHandling\EventBusInterface;
use Broadway\EventSourcing\EventSourcingRepository;
use Broadway\EventSourcing\EventStreamDecoratorInterface;
use Broadway\EventStore\EventStoreInterface;

class OrganizerRepository extends EventSourcingRepository
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
        $this->aggregateClass        = Organizer::class;
        $this->eventStreamDecorators = $eventStreamDecorators;

    }
}
