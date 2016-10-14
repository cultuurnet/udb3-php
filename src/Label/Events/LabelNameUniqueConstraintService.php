<?php

namespace CultuurNet\UDB3\Label\Events;

use Broadway\Domain\DomainMessage;
use CultuurNet\UDB3\EventSourcing\DBAL\UniqueConstraintServiceInterface;
use ValueObjects\String\String as StringLiteral;

class LabelNameUniqueConstraintService implements UniqueConstraintServiceInterface
{
    /**
     * @param DomainMessage $domainMessage
     * @return bool
     */
    public function hasUniqueConstraint(DomainMessage $domainMessage)
    {
        $event = $domainMessage->getPayload();

        return ($event instanceof Created ||
            $event instanceof CopyCreated);
    }

    /**
     * @param DomainMessage $domainMessage
     * @return StringLiteral
     */
    public function getUniqueConstraintValue(DomainMessage $domainMessage)
    {
        /** @var Created|CopyCreated $event */
        $event = $domainMessage->getPayload();

        return $event->getName();
    }
}
