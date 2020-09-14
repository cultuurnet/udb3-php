<?php

namespace CultuurNet\UDB3\Event;

use Broadway\Serializer\SerializableInterface;
use InvalidArgumentException;

abstract class EventEvent implements SerializableInterface
{
    /**
     * @var string
     */
    protected $eventId;

    public function __construct(string $eventId)
    {
        $this->eventId = $eventId;
    }

    public function getEventId(): string
    {
        return $this->eventId;
    }

    public function serialize(): array
    {
        return array(
            'event_id' => $this->eventId,
        );
    }
}
