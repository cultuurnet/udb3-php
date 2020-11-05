<?php

declare(strict_types=1);

namespace CultuurNet\UDB3\Event\Events\Status;

use CultuurNet\UDB3\Timestamp;

abstract class SubEventStatusUpdated
{
    /**
     * @var string
     */
    private $eventId;

    /**
     * @var Timestamp
     */
    private $timestamp;

    /**
     * @var string
     */
    private $reason;

    final public function __construct(string $eventId, Timestamp $timestamp, string $reason)
    {
        $this->eventId = $eventId;
        $this->timestamp = $timestamp;
        $this->reason = $reason;
    }

    public function serialize(): array
    {
        return [
            'eventId' => $this->eventId,
            'timestamp' => $this->timestamp->serialize(),
            'reason' => $this->reason,
        ];
    }

    /**
     * @return static
     */
    public static function deserialize(array $data)
    {
        return new static(
            $data['eventId'],
            Timestamp::deserialize($data['timestamp']),
            $data['reason']
        );
    }

    public function getEventId(): string
    {
        return $this->eventId;
    }

    public function getTimestamp(): Timestamp
    {
        return $this->timestamp;
    }

    public function getReason(): string
    {
        return $this->reason;
    }
}
