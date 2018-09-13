<?php

namespace CultuurNet\UDB3;

use Broadway\Domain\DomainMessage;
use DateTime;

class DomainMessageAdapter
{
    /**
     * @var DomainMessage
     */
    private $domainMessage;

    /**
     * DomainMessageAdapter constructor.
     *
     * @param DomainMessage $domainMessage
     */
    public function __construct(DomainMessage $domainMessage)
    {
        $this->domainMessage = $domainMessage;
    }

    public function getUserId(): string
    {
        $metaData = $this->domainMessage->getMetadata()->serialize();
        return $metaData['user_id'] ?? '';
    }

    public function getRecordedDateTime(): ?DateTime
    {
        return new DateTime($this->domainMessage->getRecordedOn()->toString());
    }
}
