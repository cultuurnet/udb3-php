<?php

namespace CultuurNet\UDB3\Event;

use Broadway\CommandHandling\CommandBusInterface;
use Broadway\Repository\RepositoryInterface;
use Broadway\UuidGenerator\UuidGeneratorInterface;
use CultuurNet\UDB3\CalendarInterface;
use CultuurNet\UDB3\Event\Commands\DeleteEvent;
use CultuurNet\UDB3\Event\Commands\UpdateMajorInfo;
use CultuurNet\UDB3\Event\ReadModel\DocumentRepositoryInterface;
use CultuurNet\UDB3\EventServiceInterface;
use CultuurNet\UDB3\InvalidTranslationLanguageException;
use CultuurNet\UDB3\Language;
use CultuurNet\UDB3\LanguageCanBeTranslatedToSpecification;
use CultuurNet\UDB3\Location;
use CultuurNet\UDB3\Offer\Commands\OfferCommandFactoryInterface;
use CultuurNet\UDB3\Offer\DefaultOfferEditingService;
use CultuurNet\UDB3\OfferEditingInterface;
use CultuurNet\UDB3\PlaceService;
use CultuurNet\UDB3\Title;

class DefaultEventEditingService extends DefaultOfferEditingService implements
    EventEditingServiceInterface,
    OfferEditingInterface
{

    use \CultuurNet\UDB3\OfferEditingTrait;

    /**
     * @var EventServiceInterface
     */
    protected $eventService;

    /**
     * @var PlaceService
     */
    protected $places;

    /**
     * @var RepositoryInterface
     */
    protected $writeRepository;

    /**
     * @param EventServiceInterface $eventService
     * @param CommandBusInterface $commandBus
     * @param UuidGeneratorInterface $uuidGenerator
     */
    public function __construct(
        EventServiceInterface $eventService,
        CommandBusInterface $commandBus,
        UuidGeneratorInterface $uuidGenerator,
        DocumentRepositoryInterface $readRepository,
        PlaceService $placeService,
        OfferCommandFactoryInterface $commandFactory,
        RepositoryInterface $writeRepository
    ) {
        parent::__construct($commandBus, $uuidGenerator, $readRepository, $commandFactory);
        $this->eventService = $eventService;
        $this->places = $placeService;
        $this->writeRepository = $writeRepository;
    }

    protected function guardTranslationLanguage(Language $language)
    {
        if (!LanguageCanBeTranslatedToSpecification::isSatisfiedBy($language)) {
            throw new InvalidTranslationLanguageException($language);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function createEvent(Title $title, EventType $eventType, Location $location, CalendarInterface $calendar, $theme = null)
    {
        $eventId = $this->uuidGenerator->generate();

        $event = Event::create($eventId, $title, $eventType, $location, $calendar, $theme);

        $this->writeRepository->save($event);

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
     * {@inheritdoc}
     */
    public function deleteEvent($eventId)
    {
        $this->guardId($eventId);

        return $this->commandBus->dispatch(
            new DeleteEvent($eventId)
        );
    }
}
