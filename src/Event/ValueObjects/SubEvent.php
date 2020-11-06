<?php

declare(strict_types=1);

namespace CultuurNet\UDB3\Event\ValueObjects;

use CultuurNet\UDB3\Timestamp;

final class SubEvent
{
    /**
     * @var Timestamp
     */
    private $timestamp;

    /**
     * @var Status
     */
    private $status;

    public function __construct(Timestamp $timestamp, Status $status)
    {
        $this->timestamp = $timestamp;
        $this->status = $status;
    }

    public function getTimestamp(): Timestamp
    {
        return $this->timestamp;
    }

    public function getStatus(): Status
    {
        return $this->status;
    }

    public function equals(SubEvent $otherSubEvent): bool
    {
        if (!$this->timestamp->equals($otherSubEvent->getTimestamp())) {
            return false;
        }

        if (!$this->status->equals($otherSubEvent->getStatus())) {
            return false;
        }

        return true;
    }
}
