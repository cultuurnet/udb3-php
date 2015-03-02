<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Place;

use Broadway\CommandHandling\CommandBusInterface;
use Broadway\Repository\AggregateNotFoundException;
use Broadway\Repository\RepositoryInterface;
use Broadway\UuidGenerator\UuidGeneratorInterface;
use CultuurNet\UDB3\CalendarInterface;
use CultuurNet\UDB3\Event\EventType;
use CultuurNet\UDB3\Event\Title;
use CultuurNet\UDB3\Location;
use CultuurNet\UDB3\OfferEditingInterface;

class DefaultPlaceEditingService implements PlaceEditingServiceInterface, OfferEditingInterface
{

    use \CultuurNet\UDB3\OfferEditingTrait;

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
    protected $placeRepository;

    /**
     * @param CommandBusInterface $commandBus
     * @param UuidGeneratorInterface $uuidGenerator
     * @param RepositoryInterface $placeRepository
     */
    public function __construct(
        CommandBusInterface $commandBus,
        UuidGeneratorInterface $uuidGenerator,
        RepositoryInterface $placeRepository
    ) {
        $this->commandBus = $commandBus;
        $this->uuidGenerator = $uuidGenerator;
        $this->placeRepository = $placeRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function createPlace(Title $title, EventType $eventType, Location $location, CalendarInterface $calendar, $theme = NULL)
    {
        $id = $this->uuidGenerator->generate();

        $place = Place::createPlace($id, $title, $eventType, $location, $calendar, $theme);

        $this->placeRepository->add($place);

        return $id;
    }

    /**
     * @param string $id
     * @throws AggregateNotFoundException
     */
    public function guardId($id)
    {
        // This validates if the id is valid.
        return $this->placeRepository->load($id);
    }

}
