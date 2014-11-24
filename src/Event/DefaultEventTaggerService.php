<?php


namespace CultuurNet\UDB3\Event;

use Broadway\CommandHandling\CommandBusInterface;
use CultuurNet\UDB3\EventServiceInterface;
use CultuurNet\UDB3\Keyword;

/**
 * The default event tagger service that uses an event service to validate ids and a command bus to do the actual tagging.
 */
class DefaultEventTaggerService implements EventTaggerServiceInterface
{
    /**
     * @var EventServiceInterface
     */
    protected $eventService;

    /**
     * @var CommandBusInterface
     */
    protected $commandBus;

    /**
     * @param EventServiceInterface $eventService
     * @param CommandBusInterface $commandBus
     */
    public function __construct(
        EventServiceInterface $eventService,
        CommandBusInterface $commandBus
    ) {
        $this->eventService = $eventService;
        $this->commandBus = $commandBus;
    }

    /**
     * {@inheritdoc}
     * @return string The id of the command that's doing the tagging.
     */
    public function tagEventsById($eventIds, Keyword $keyword)
    {
        if (!isset($eventIds) || count($eventIds) == 0) {
            throw new \InvalidArgumentException('no event Ids to tag');
        }

        $events = [];

        foreach ($eventIds as $eventId) {
            $events[] = $this->eventService->getEvent($eventId);
        }

        $command = new TagEvents($eventIds, $keyword);
        $commandId = $this->commandBus->dispatch($command);

        return $commandId;
    }

    /**
     * {@inheritdoc}
     */
    public function tagQuery($query, Keyword $keyword)
    {
        if (!isset($query) || strlen($query) == 0) {
            throw new \InvalidArgumentException('query should not be empty');
        }

        $command = new TagQuery($query, $keyword);
        $commandId = $this->commandBus->dispatch($command);

        return $commandId;
    }
}
