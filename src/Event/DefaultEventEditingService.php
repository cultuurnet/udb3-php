<?php

namespace CultuurNet\UDB3\Event;

use Broadway\CommandHandling\CommandBusInterface;
use Broadway\Repository\AggregateNotFoundException;
use Broadway\Repository\RepositoryInterface;
use Broadway\UuidGenerator\UuidGeneratorInterface;
use CultuurNet\UDB3\CalendarInterface;
use CultuurNet\UDB3\Event\Commands\UpdateAudience;
use CultuurNet\UDB3\Event\Commands\UpdateLocation;
use CultuurNet\UDB3\Event\Commands\UpdateMajorInfo;
use CultuurNet\UDB3\Event\ReadModel\DocumentRepositoryInterface;
use CultuurNet\UDB3\Event\ValueObjects\Audience;
use CultuurNet\UDB3\Label\LabelServiceInterface;
use CultuurNet\UDB3\Location\Location;
use CultuurNet\UDB3\Offer\Commands\OfferCommandFactoryInterface;
use CultuurNet\UDB3\Offer\DefaultOfferEditingService;
use CultuurNet\UDB3\Title;

class DefaultEventEditingService extends DefaultOfferEditingService implements EventEditingServiceInterface
{
    /**
     * @var EventServiceInterface
     */
    protected $eventService;

    /**
     * @var RepositoryInterface
     */
    protected $writeRepository;

    /**
     * @param EventServiceInterface $eventService
     * @param CommandBusInterface $commandBus
     * @param UuidGeneratorInterface $uuidGenerator
     * @param DocumentRepositoryInterface $readRepository
     * @param OfferCommandFactoryInterface $commandFactory
     * @param RepositoryInterface $writeRepository
     * @param LabelServiceInterface $labelService
     */
    public function __construct(
        EventServiceInterface $eventService,
        CommandBusInterface $commandBus,
        UuidGeneratorInterface $uuidGenerator,
        DocumentRepositoryInterface $readRepository,
        OfferCommandFactoryInterface $commandFactory,
        RepositoryInterface $writeRepository,
        LabelServiceInterface $labelService
    ) {
        parent::__construct(
            $commandBus,
            $uuidGenerator,
            $readRepository,
            $commandFactory,
            $labelService
        );
        $this->eventService = $eventService;
        $this->writeRepository = $writeRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function createEvent(
        Title $title,
        EventType $eventType,
        Location $location,
        CalendarInterface $calendar,
        $theme = null
    ) {
        $eventId = $this->uuidGenerator->generate();

        $event = Event::create(
            $eventId,
            $title,
            $eventType,
            $location,
            $calendar,
            $theme,
            $this->publicationDate
        );

        $this->writeRepository->save($event);

        return $eventId;
    }

    /**
     * @inheritdoc
     */
    public function copyEvent($originalEventId, CalendarInterface $calendar)
    {
        if (!is_string($originalEventId)) {
            throw new \InvalidArgumentException(
                'Expected originalEventId to be a string, received ' . gettype($originalEventId)
            );
        }

        try {
            /** @var Event $event */
            $event = $this->writeRepository->load($originalEventId);
        } catch (AggregateNotFoundException $exception) {
            throw new \InvalidArgumentException(
                'No original event found to copy with id ' . $originalEventId
            );
        }

        $eventId = $this->uuidGenerator->generate();

        $newEvent = $event->copy($eventId, $calendar);

        $this->writeRepository->save($newEvent);

        return $eventId;
    }

    /**
     * {@inheritdoc}
     */
    public function updateMajorInfo($eventId, Title $title, EventType $eventType, Location $location, CalendarInterface $calendar, $theme = null)
    {
        $this->guardId($eventId);

        return $this->commandBus->dispatch(
            new UpdateMajorInfo($eventId, $title, $eventType, $location, $calendar, $theme)
        );
    }

    /**
     * @inheritdoc
     */
    public function updateLocation($eventId, Location $location)
    {
        $this->guardId($eventId);

        return $this->commandBus->dispatch(
            new UpdateLocation($eventId, $location)
        );
    }

    /**
     * @inheritdoc
     */
    public function updateAudience($eventId, Audience $audience)
    {
        return $this->commandBus->dispatch(
            new UpdateAudience($eventId, $audience)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function deleteEvent($eventId)
    {
        return $this->delete($eventId);
    }
}
