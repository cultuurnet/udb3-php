<?php


namespace CultuurNet\UDB3\Event;


use Broadway\CommandHandling\CommandBusInterface;
use CultuurNet\UDB3\EventServiceInterface;
use CultuurNet\UDB3\EventNotFoundException;

class EventTaggerService {

    /**
     * @var EventServiceInterface
     */
    protected $eventService;

    /**
     * @var CommandBusInterface
     */
    protected $commandBus;

    public function __construct(
        EventServiceInterface $eventService,
        CommandBusInterface $commandBus
    )
    {
        $this->eventService = $eventService;
        $this->commandBus = $commandBus;
    }

    /**
     * @param $eventIds string[]
     * @param $keyword string
     * @return string command id
     * @throws EventNotFoundException
     * @throws \Exception
     */
    public function tagEventsById($eventIds, $keyword)
    {
        if(!isset($keyword) || strlen( $keyword ) == 0) {
            throw new \Exception('invalid keyword');
        }

        if(!isset($eventIds) || count($eventIds) == 0) {
            throw new \Exception('no event Ids to tag');
        }

        $events = [];

        foreach($eventIds as $eventId) {
            $events[] = $this->eventService->getEvent($eventId);
        }

        $command = new TagEvents($eventIds, $keyword);
        $commandId = $this->commandBus->dispatch($command);

        return $commandId;
    }
} 