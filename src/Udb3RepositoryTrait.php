<?php

/**
 * @file
 * Contains CultuurNet\UDB3\Udb3RepositoryTrait.
 */

namespace CultuurNet\UDB3;

use Broadway\Domain\AggregateRoot;
use Broadway\Domain\DomainEventStream;

/**
 * Trait for UDB3 repositories.
 */
trait Udb3RepositoryTrait
{

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
