<?php

/**
 * @file
 * Contains CultuurNet\UDB3\Udb3RepositoryTrait.
 */

namespace CultuurNet\UDB3;

use Assert\Assertion;
use Broadway\Domain\AggregateRoot;
use Broadway\Domain\DomainEventStream;
use Broadway\EventStore\EventStreamNotFoundException;
use Broadway\Repository\AggregateNotFoundException;

/**
 * Trait for UDB3 repositories.
 */
trait Udb3RepositoryTrait
{

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
