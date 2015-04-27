<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\UsedLabelsMemory;

use Broadway\EventHandling\EventBusInterface;
use Broadway\EventSourcing\EventSourcingRepository;
use Broadway\EventStore\EventStoreInterface;

class UsedLabelsMemoryRepository extends EventSourcingRepository
{
    public function __construct(
        EventStoreInterface $eventStore,
        EventBusInterface $eventBus,
        array $eventStreamDecorators = array()
    ) {
        parent::__construct(
            $eventStore,
            $eventBus,
            UsedLabelsMemory::class,
            $eventStreamDecorators
        );
    }
}
