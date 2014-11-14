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
     * @return Event
     */
    static public function create($eventId)
    {
        $event = new self();
        $event->apply(new EventCreated($eventId));

        return $event;
    }

    static public function importFromUDB2($eventId, $cdbXml)
    {
        $event = new self();
        $event->apply(new EventImportedFromUDB2($eventId, $cdbXml));

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

    protected function applyEventImportedFromUDB2(EventImportedFromUDB2 $eventImported)
    {
        $this->eventId = $eventImported->getEventId();
        $cdbXml = $eventImported->getCdbXml();

        $udb2SimpleXml = new \SimpleXMLElement(
            $cdbXml,
            0,
            false,
            \CultureFeed_Cdb_Default::CDB_SCHEME_URL
        );

        $udb2Event = \CultureFeed_Cdb_Item_Event::parseFromCdbXml(
            $udb2SimpleXml
        );

        $this->keywords = array_values($udb2Event->getKeywords());
    }
}
