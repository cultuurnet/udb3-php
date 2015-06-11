<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Variations;

use Broadway\EventHandling\EventBusInterface;
use Broadway\EventSourcing\AggregateFactory\PublicConstructorAggregateFactory;
use Broadway\EventSourcing\EventSourcingRepository;
use Broadway\EventStore\EventStoreInterface;
use CultuurNet\UDB3\Variations\Model\EventVariation;

class EventVariationRepository extends EventSourcingRepository
{
    public function __construct(
        EventStoreInterface $eventStore,
        EventBusInterface $eventBus,
        array $eventStreamDecorators = array()
    ) {
        parent::__construct(
            $eventStore,
            $eventBus,
            EventVariation::class,
            new PublicConstructorAggregateFactory(),
            $eventStreamDecorators
        );
    }
}
