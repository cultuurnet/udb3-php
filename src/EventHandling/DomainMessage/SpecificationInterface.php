<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\EventHandling\DomainMessage;

use Broadway\Domain\DomainMessage;

interface SpecificationInterface
{
    public function isSatisfiedBy(DomainMessage $domainMessage);
}
