<?php

namespace CultuurNet\UDB3\Organizer\Events;

use Broadway\Domain\DomainMessage;
use CultuurNet\UDB3\EventSourcing\DBAL\UniqueConstraintServiceInterface;
use ValueObjects\String\String as StringLiteral;

class WebsiteUniqueConstraintService implements UniqueConstraintServiceInterface
{
    /**
     * @inheritdoc
     */
    public function hasUniqueConstraint(DomainMessage $domainMessage)
    {
        return $domainMessage->getPayload() instanceof OrganizerCreatedWithUniqueWebsite;
    }

    /**
     * @inheritdoc
     */
    public function getUniqueConstraintValue(DomainMessage $domainMessage)
    {
        if (!$this->hasUniqueConstraint($domainMessage)) {
            throw new \InvalidArgumentException('Given domain message has no unique constraint.');
        }

        /* @var OrganizerCreatedWithUniqueWebsite $payload */
        $payload = $domainMessage->getPayload();
        return new StringLiteral((string) $payload->getWebsite());
    }
}
