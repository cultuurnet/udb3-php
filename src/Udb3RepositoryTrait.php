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

    private function getType()
    {
        return $this->aggregateClass;
    }
}
