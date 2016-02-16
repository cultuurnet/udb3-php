<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Variations;

use Broadway\EventHandling\EventBusInterface;
use Broadway\EventSourcing\AggregateFactory\PublicConstructorAggregateFactory;
use Broadway\EventSourcing\EventSourcingRepository;
use Broadway\EventStore\EventStoreInterface;
use CultuurNet\UDB3\Variations\Model\OfferVariation;

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
            OfferVariation::class,
            new PublicConstructorAggregateFactory(),
            $eventStreamDecorators
        );
    }

    /**
     * {@inheritDoc}
     * @return OfferVariation
     * @throws AggregateDeletedException
     */
    public function load($id)
    {
        /** @var Deleteable $variationAggregate */
        $variationAggregate = parent::load($id);

        if ($variationAggregate->isDeleted()) {
            throw AggregateDeletedException::create($id);
        }

        return $variationAggregate;
    }
}
