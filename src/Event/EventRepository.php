<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Event;


use Broadway\EventHandling\EventBusInterface;
use Broadway\EventSourcing\EventSourcingRepository;
use Broadway\EventStore\EventStoreInterface;
use Broadway\Repository\AggregateNotFoundException;
use CultuurNet\Search\Parameter\Query;
use CultuurNet\UDB3\SearchAPI2\SearchServiceInterface;

class EventRepository extends EventSourcingRepository
{

    protected $search;

    public function __construct(
        EventStoreInterface $eventStore,
        EventBusInterface $eventBus,
        SearchServiceInterface $search
    ) {
        parent::__construct(
            $eventStore,
            $eventBus,
            '\CultuurNet\UDB3\Event\Event'
        );

        $this->search = $search;
    }

    /**
     * Ensures an event is created, by importing it from UDB2 if it does not
     * exist locally yet.
     *
     * @param string $eventId
     * @return Event
     */
    public function ensureEventCreated($eventId)
    {
        /** @var Event $event */
        try {
            $event = $this->load($eventId);
        } catch (AggregateNotFoundException $e) {
            $results = $this->search->search(new Query('cdbid:' . $eventId));

            $cdbxml = $results->getBody(true);

            $reader = new \XMLReader();

            $reader->open($cdbxml);

            while ($reader->read()) {
                switch ($reader->nodeType) {
                    case ($reader::ELEMENT):
                        if ($reader->localName == "event" &&
                            $reader->getAttribute('cdbid') == $eventId
                        ) {
                            $node = $reader->expand();
                            $dom = new \DomDocument('1.0');
                            $n = $dom->importNode($node, true);
                            $dom->appendChild($n);
                            $eventXml = $dom->saveXML();
                        }
                }
            }

            if (!$eventXml) {
                // @todo Better exception handling.
                throw new \RuntimeException('Event not found.');
            }

            $event = Event::importFromUDB2($eventId, $eventXml);
        }



        return $event;
    }
} 
