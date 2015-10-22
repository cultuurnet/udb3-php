<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Place;

use Broadway\CommandHandling\CommandBusInterface;
use Broadway\Repository\AggregateNotFoundException;
use Broadway\Repository\RepositoryInterface;
use Broadway\UuidGenerator\UuidGeneratorInterface;
use CultuurNet\UDB3\Address;
use CultuurNet\UDB3\CalendarInterface;
use CultuurNet\UDB3\Event\EventType;
use CultuurNet\UDB3\OfferEditingInterface;
use CultuurNet\UDB3\Place\Commands\DeletePlace;
use CultuurNet\UDB3\Place\Commands\UpdateFacilities;
use CultuurNet\UDB3\Place\Commands\UpdateMajorInfo;
use CultuurNet\UDB3\Theme;
use CultuurNet\UDB3\Title;

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
    public function createPlace(Title $title, EventType $eventType, Address $address, CalendarInterface $calendar, Theme $theme = null)
    {
        $id = $this->uuidGenerator->generate();

        $place = Place::createPlace($id, $title, $eventType, $address, $calendar, $theme);

        $this->placeRepository->save($place);

        return $id;
    }

    /**
     * {@inheritdoc}
     */
    public function updateMajorInfo($id, Title $title, EventType $eventType, Address $address, CalendarInterface $calendar, $theme = null)
    {
        $this->guardId($id);

        return $this->commandBus->dispatch(
            new UpdateMajorInfo($id, $title, $eventType, $address, $calendar, $theme)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function deletePlace($id)
    {
        $this->guardId($id);

        return $this->commandBus->dispatch(
            new DeletePlace($id)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function updateFacilities($id, array $facilities)
    {

        $this->guardId($id);

        return $this->commandBus->dispatch(
            new UpdateFacilities($id, $facilities)
        );
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
