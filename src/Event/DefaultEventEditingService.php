<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Event;


use Broadway\CommandHandling\CommandBusInterface;
use CultuurNet\UDB3\EventNotFoundException;
use CultuurNet\UDB3\EventServiceInterface;
use CultuurNet\UDB3\Language;

class DefaultEventEditingService implements EventEditingServiceInterface
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
     */
    public function translateTitle($eventId, Language $language, $title)
    {
        $this->guardEventId($eventId);

        return $this->commandBus->dispatch(
            new TranslateTitle($eventId, $language, $title)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function translateDescription($eventId, Language $language, $description)
    {
        $this->guardEventId($eventId);

        return $this->commandBus->dispatch(
            new TranslateDescription($eventId, $language, $description)
        );
    }

    /**
     * @param string $eventId
     * @throws EventNotFoundException
     */
    protected function guardEventId($eventId)
    {
        // This validates if the eventId is valid.
        $this->eventService->getEvent($eventId);
    }
} 
