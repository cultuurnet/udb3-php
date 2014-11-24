<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Event;

use Broadway\Serializer\SerializableInterface;

abstract class EventEvent implements SerializableInterface
{
    protected $eventId;

    public function __construct($eventId)
    {
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
