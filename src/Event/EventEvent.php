<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Event;


abstract class EventEvent
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
}
