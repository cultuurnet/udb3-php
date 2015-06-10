<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Event;

use Broadway\CommandHandling\CommandBusInterface;
use Broadway\Repository\RepositoryInterface;
use CultuurNet\UDB3\EntityNotFoundException;
use CultuurNet\UDB3\Event\Commands\ApplyLabel;
use CultuurNet\UDB3\Event\Commands\Unlabel;
use CultuurNet\UDB3\Event\Editing\EditDescription;
use CultuurNet\UDB3\Event\Editing\EditPurpose;
use CultuurNet\UDB3\EventNotFoundException;
use CultuurNet\UDB3\EventServiceInterface;
use CultuurNet\UDB3\InvalidTranslationLanguageException;
use CultuurNet\UDB3\Label;
use CultuurNet\UDB3\Language;
use CultuurNet\UDB3\LanguageCanBeTranslatedToSpecification;
use Broadway\UuidGenerator\UuidGeneratorInterface;
use CultuurNet\UDB3\PlaceService;

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
        $this->guardEventId($eventId);
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
        $this->guardEventId($eventId);
        $this->guardTranslationLanguage($language);

        return $this->commandBus->dispatch(
            new TranslateDescription($eventId, $language, $description)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function editDescription($eventId, $editorId, EditPurpose $purpose, $description)
    {
        $this->guardEventId($eventId);

        return $this->commandBus->dispatch(
            new EditDescription($eventId, $editorId, $purpose, $description)
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
        $this->guardEventId($eventId);

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
        $this->guardEventId($eventId);

        return $this->commandBus->dispatch(
            new Unlabel($eventId, $label)
        );
    }

    /**
     * @param Title $title
     * @param string $location
     * @param mixed $date
     *
     * @return string $eventId
     *
     * @throws EntityNotFoundException If the location can not be found.
     */
    public function createEvent(Title $title, $location, $date)
    {
        $eventId = $this->uuidGenerator->generate();

        // This will throw an EntityNotFoundException if the place does
        // not exist.
        $this->places->getEntity($location);

        $type = new EventType('0.50.4.0.0', 'concert');
        $event = Event::create($eventId, $title, $location, $date, $type);

        $this->eventRepository->save($event);

        return $eventId;
    }
}
