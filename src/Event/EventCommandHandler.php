<?php


namespace CultuurNet\UDB3\Event;

use Broadway\CommandHandling\CommandHandler;
use Broadway\Repository\RepositoryInterface;

class EventCommandHandler extends CommandHandler
{
    /**
     * @var RepositoryInterface
     */
    protected $eventRepository;

    public function __construct(RepositoryInterface $eventRepository)
    {
        $this->eventRepository = $eventRepository;
    }

    public function handleTagEvents(TagEvents $tagEvents)
    {
        foreach ($tagEvents->getEventIds() as $eventId) {
            /** @var Event $event */
            $event = $this->eventRepository->load($eventId);
            $event->tag($tagEvents->getKeyword());
            $this->eventRepository->add($event);
        }
    }
}
