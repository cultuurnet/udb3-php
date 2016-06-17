<?php

namespace CultuurNet\UDB3\Label\Events;

use Broadway\Domain\DomainMessage;
use CultuurNet\UDB3\EventSourcing\DBAL\UniqueHelperInterface;
use ValueObjects\String\String as StringLiteral;

class UniqueHelper implements UniqueHelperInterface
{
    /**
     * @param DomainMessage $domainMessage
     * @return bool
     */
    public function requiresUnique(DomainMessage $domainMessage)
    {
        $event = $domainMessage->getPayload();

        return ($event instanceof Created ||
            $event instanceof CopyCreated);
    }

    /**
     * @param DomainMessage $domainMessage
     * @return StringLiteral
     */
    public function getUnique(DomainMessage $domainMessage)
    {
        /** @var Created|CopyCreated $event */
        $event = $domainMessage->getPayload();

        return $event->getName();
    }
}
