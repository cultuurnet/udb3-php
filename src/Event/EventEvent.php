<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Event;

use Broadway\Serializer\SerializableInterface;

abstract class EventEvent implements SerializableInterface
{
    protected $eventId;

    /**
     * @param string $eventId
     */
    public function __construct($eventId)
    {
        if (!is_string($eventId)) {
            throw new \InvalidArgumentException(
                'Expected eventId to be a string, received ' . gettype($eventId)
            );
        }

        $this->eventId = $eventId;
    }

    public function getEventId()
    {
        return $this->eventId;
    }

    /**
     * @return array
     */
    public function serialize()
    {
        return array(
            'event_id' => $this->eventId,
        );
    }

    /**
     * @return mixed The object instance
     */
    public static function deserialize(array $data)
    {
        return new static($data['event_id']);
    }
}
