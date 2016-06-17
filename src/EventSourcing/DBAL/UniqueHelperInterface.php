<?php

namespace CultuurNet\UDB3\EventSourcing\DBAL;

use Broadway\Domain\DomainMessage;
use ValueObjects\String\String as StringLiteral;

interface UniqueHelperInterface
{
    /**
     * @param DomainMessage $domainMessage
     * @return bool
     */
    public function requiresUnique(DomainMessage $domainMessage);

    /**
     * @param DomainMessage $domainMessage
     * @return StringLiteral
     */
    public function getUnique(DomainMessage $domainMessage);
}
