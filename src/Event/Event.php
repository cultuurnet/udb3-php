<?php

namespace CultuurNet\UDB3\Event;

use Broadway\EventSourcing\EventSourcedAggregateRoot;

class Event extends EventSourcedAggregateRoot
{
    protected $eventId;
    protected $keywords = array();

    /**
     * Factory method to create a new event.
     *
     * @param string $eventId
     */
    static public function create($eventId)
    {
        $event = new self();
        $event->apply(new EventCreated($eventId));

        return $event;
    }

    /**
     * {@inheritdoc}
     */
    public function getAggregateRootId()
    {
        return $this->eventId;
    }

    public function getKeywords()
    {
        return $this->keywords;
    }

    public function tag($keyword)
    {
        if (in_array($keyword, $this->keywords)) {
            return;
        }

        $this->apply(new EventWasTagged($this->eventId, $keyword));
    }

    protected function applyEventCreated(EventCreated $eventCreated)
    {
        $this->eventId = $eventCreated->getEventId();
    }

    protected function applyEventWasTagged(EventWasTagged $eventTagged)
    {
        $this->keywords[] = $eventTagged->getKeyword();
    }
}
