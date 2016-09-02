<?php

namespace CultuurNet\UDB3\Place;

use Broadway\CommandHandling\CommandBusInterface;
use Broadway\Repository\RepositoryInterface;
use Broadway\UuidGenerator\UuidGeneratorInterface;
use CultuurNet\UDB3\Address;
use CultuurNet\UDB3\CalendarInterface;
use CultuurNet\UDB3\Event\EventType;
use CultuurNet\UDB3\Event\ReadModel\DocumentRepositoryInterface;
use CultuurNet\UDB3\Offer\Commands\OfferCommandFactoryInterface;
use CultuurNet\UDB3\Offer\DefaultOfferEditingService;
use CultuurNet\UDB3\Place\Commands\DeletePlace;
use CultuurNet\UDB3\Place\Commands\UpdateFacilities;
use CultuurNet\UDB3\Place\Commands\UpdateMajorInfo;
use CultuurNet\UDB3\Theme;
use CultuurNet\UDB3\Title;

class DefaultPlaceEditingService extends DefaultOfferEditingService implements PlaceEditingServiceInterface
{
    /**
     * @var RepositoryInterface
     */
    protected $writeRepository;

    public function __construct(
        CommandBusInterface $commandBus,
        UuidGeneratorInterface $uuidGenerator,
        DocumentRepositoryInterface $readRepository,
        OfferCommandFactoryInterface $commandFactory,
        RepositoryInterface $writeRepository
    ) {
        parent::__construct(
            $commandBus,
            $uuidGenerator,
            $readRepository,
            $commandFactory
        );

        $this->writeRepository = $writeRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function createPlace(
        Title $title,
        EventType $eventType,
        Address $address,
        CalendarInterface $calendar,
        Theme $theme = null
    ) {
        $id = $this->uuidGenerator->generate();

        $place = Place::createPlace($id, $title, $eventType, $address, $calendar, $theme, $this->publicationDate);

        $this->writeRepository->save($place);

        return $id;
    }

    /**
     * {@inheritdoc}
     */
    public function updateMajorInfo($id, Title $title, EventType $eventType, Address $address, CalendarInterface $calendar, Theme $theme = null)
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
        return $this->delete($id);
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
}
