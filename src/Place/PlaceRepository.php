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
        parent::__construct(
            $eventStore,
            $eventBus,
            '\CultuurNet\UDB3\Place\Place',
            $eventStreamDecorators
        );
    }
}
