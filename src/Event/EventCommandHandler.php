<?php


namespace CultuurNet\UDB3\Event;

use Broadway\CommandHandling\CommandHandler;

class EventCommandHandler extends CommandHandler
{
    /**
     * @var EventRepository
     */
    protected $eventRepository;

    public function __construct(EventRepository $eventRepository)
    {
        $this->eventRepository = $eventRepository;
    }

    public function handleTagEvents(TagEvents $tagEvents)
    {
        foreach ($tagEvents->getEventIds() as $eventId) {
            $event = $this->eventRepository->ensureEventCreated($eventId);
            $event->tag($tagEvents->getKeyword());
            $this->eventRepository->add($event);
        }
    }
}
