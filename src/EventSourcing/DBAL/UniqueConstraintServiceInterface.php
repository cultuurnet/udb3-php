<?php

namespace CultuurNet\UDB3\EventSourcing\DBAL;

use Broadway\Domain\DomainMessage;
use ValueObjects\String\String as StringLiteral;

interface UniqueConstraintServiceInterface
{
    /**
     * @param DomainMessage $domainMessage
     * @return bool
     */
    public function hasUniqueConstraint(DomainMessage $domainMessage);

    /**
     * @param DomainMessage $domainMessage
     * @return StringLiteral
     */
    public function getUniqueConstraintValue(DomainMessage $domainMessage);
}
