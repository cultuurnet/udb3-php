<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Event;


use Broadway\EventHandling\EventBusInterface;
use Broadway\EventSourcing\EventSourcingRepository;
use Broadway\EventStore\EventStoreInterface;

class EventRepository extends EventSourcingRepository {

    public function __construct(EventStoreInterface $eventStore, EventBusInterface $eventBus)
    {
        parent::__construct($eventStore, $eventBus, '\CultuurNet\UDB3\Event\Event');
    }
} 
