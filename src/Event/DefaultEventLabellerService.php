<?php


namespace CultuurNet\UDB3\Event;

use Broadway\CommandHandling\CommandBusInterface;
use CultuurNet\UDB3\EventServiceInterface;
use CultuurNet\UDB3\Label;

/**
 * The default event labeller service that uses an event service to validate ids
 * and a command bus to do the actual labelling.
 */
class DefaultEventLabellerService implements EventLabellerServiceInterface
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
     * @return string The id of the command that's doing the labelling.
     */
    public function labelEventsById($eventIds, Label $label)
    {
        if (!isset($eventIds) || count($eventIds) == 0) {
            throw new \InvalidArgumentException('no event Ids to label');
        }

        // By retrieving the events first by their ID, we ensure
        // the IDs are actually valid.
        foreach ($eventIds as $eventId) {
            $this->eventService->getEvent($eventId);
        }

        $command = new LabelEvents($eventIds, $label);
        $commandId = $this->commandBus->dispatch($command);

        return $commandId;
    }

    /**
     * {@inheritdoc}
     */
    public function labelQuery($query, Label $label)
    {
        if (!isset($query) || strlen($query) == 0) {
            throw new \InvalidArgumentException('query should not be empty');
        }

        $command = new LabelQuery($query, $label);
        $commandId = $this->commandBus->dispatch($command);

        return $commandId;
    }
}
