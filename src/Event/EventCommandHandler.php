<?php


namespace CultuurNet\UDB3\Event;

use Broadway\CommandHandling\CommandHandler;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class EventCommandHandler extends CommandHandler implements LoggerAwareInterface
{
    use LoggerAwareTrait;

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

            if ($this->logger) {
                $this->logger->info(
                    'event_was_tagged',
                    array(
                        'event_id' => $eventId,
                    )
                );
            }
        }
    }
}
