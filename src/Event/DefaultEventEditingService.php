<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Event;

use Broadway\CommandHandling\CommandBusInterface;
use Broadway\Repository\RepositoryInterface;
use Broadway\UuidGenerator\UuidGeneratorInterface;
use CultuurNet\UDB3\CalendarInterface;
use CultuurNet\UDB3\Event\Commands\ApplyLabel;
use CultuurNet\UDB3\Event\Commands\DeleteEvent;
use CultuurNet\UDB3\Event\Commands\Unlabel;
use CultuurNet\UDB3\Event\Commands\UpdateMajorInfo;
use CultuurNet\UDB3\EventNotFoundException;
use CultuurNet\UDB3\EventServiceInterface;
use CultuurNet\UDB3\InvalidTranslationLanguageException;
use CultuurNet\UDB3\Label;
use CultuurNet\UDB3\Language;
use CultuurNet\UDB3\LanguageCanBeTranslatedToSpecification;
use CultuurNet\UDB3\Location;
use CultuurNet\UDB3\OfferEditingInterface;
use CultuurNet\UDB3\PlaceService;
use CultuurNet\UDB3\Title;

class DefaultEventEditingService implements EventEditingServiceInterface, OfferEditingInterface
{

    use \CultuurNet\UDB3\OfferEditingTrait;

    /**
     * @var EventServiceInterface
     */
    protected $eventService;

    /**
     * @var CommandBusInterface
     */
    protected $commandBus;

    /**
     * @var UuidGeneratorInterface
     */
    protected $uuidGenerator;

    /**
     * @var RepositoryInterface
     */
    protected $eventRepository;

    /**
     * @var PlaceService
     */
    protected $places;

    /**
     * @param EventServiceInterface $eventService
     * @param CommandBusInterface $commandBus
     * @param UuidGeneratorInterface $uuidGenerator
     */
    public function __construct(
        EventServiceInterface $eventService,
        CommandBusInterface $commandBus,
        UuidGeneratorInterface $uuidGenerator,
        RepositoryInterface $eventRepository,
        PlaceService $placeService
    ) {
        $this->eventService = $eventService;
        $this->commandBus = $commandBus;
        $this->uuidGenerator = $uuidGenerator;
        $this->eventRepository = $eventRepository;
        $this->places = $placeService;
    }

    /**
     * {@inheritdoc}
     */
    public function translateTitle($eventId, Language $language, $title)
    {
        $this->guardId($eventId);
        $this->guardTranslationLanguage($language);

        return $this->commandBus->dispatch(
            new TranslateTitle($eventId, $language, $title)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function translateDescription($eventId, Language $language, $description)
    {
        $this->guardId($eventId);
        $this->guardTranslationLanguage($language);

        return $this->commandBus->dispatch(
            new TranslateDescription($eventId, $language, $description)
        );
    }

    /**
     * @param string $id
     * @throws EventNotFoundException
     */
    public function guardId($id)
    {
        // This validates if the eventId is valid.
        $this->eventService->getEvent($id);
    }

    protected function guardTranslationLanguage(Language $language)
    {
        if (!LanguageCanBeTranslatedToSpecification::isSatisfiedBy($language)) {
            throw new InvalidTranslationLanguageException($language);
        }
    }

    /**
     * @param string $eventId
     * @param Label $label
     * @return string command id
     * @throws EventNotFoundException
     */
    public function label($eventId, Label $label)
    {
        $this->guardId($eventId);

        return $this->commandBus->dispatch(
            new ApplyLabel($eventId, $label)
        );
    }

    /**
     * @param string $eventId
     * @param Label $label
     * @return string command id
     * @throws EventNotFoundException
     */
    public function unlabel($eventId, Label $label)
    {
        $this->guardId($eventId);

        return $this->commandBus->dispatch(
            new Unlabel($eventId, $label)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function createEvent(Title $title, EventType $eventType, Location $location, CalendarInterface $calendar, $theme = null)
    {
        $eventId = $this->uuidGenerator->generate();

        // This will throw an EntityNotFoundException if the place does
        // not exist.
        //$this->places->getEntity($location);

        $event = Event::create($eventId, $title, $eventType, $location, $calendar, $theme);

        $this->eventRepository->add($event);

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
