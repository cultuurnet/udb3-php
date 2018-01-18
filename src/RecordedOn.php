<?php

namespace CultuurNet\UDB3;

use Broadway\Domain\DomainMessage;
use Broadway\Domain\DateTime;

class RecordedOn
{
    /**
     * @var DateTime
     */
    private $recorded;

    /**
     * ModifiedDateTime constructor.
     * @param DateTime $recorded
     */
    private function __construct(DateTime $recorded)
    {
        $this->recorded = $recorded;
    }

    /**
     * @param DomainMessage $domainMessage
     * @return RecordedOn
     */
    public static function fromDomainMessage(DomainMessage $domainMessage)
    {
        return new self($domainMessage->getRecordedOn());
    }

    /**
     * @param DateTime $dateTime
     * @return RecordedOn
     */
    public static function fromBroadWayDateTime(DateTime $dateTime)
    {
        return new self($dateTime);
    }

    /**
     * @return DateTime
     */
    public function getRecordedOn()
    {
        return $this->recorded;
    }

    /**
     * @return string
     */
    public function toString()
    {
        return \DateTime::createFromFormat(
            DateTime::FORMAT_STRING,
            $this->recorded->toString()
        )->format('c');
    }
}
