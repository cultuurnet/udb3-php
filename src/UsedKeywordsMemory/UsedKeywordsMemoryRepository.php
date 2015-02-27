<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\UsedKeywordsMemory;

use Broadway\EventHandling\EventBusInterface;
use Broadway\EventSourcing\EventSourcingRepository;
use Broadway\EventStore\EventStoreInterface;

class UsedKeywordsMemoryRepository extends EventSourcingRepository
{
    public function __construct(
        EventStoreInterface $eventStore,
        EventBusInterface $eventBus,
        array $eventStreamDecorators = array()
    ) {
        parent::__construct($eventStore, $eventBus, UsedKeywordsMemory::class, $eventStreamDecorators);
    }
}
