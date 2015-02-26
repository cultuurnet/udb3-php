<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Place;

use Broadway\CommandHandling\CommandBusInterface;
use Broadway\Repository\RepositoryInterface;
use Broadway\UuidGenerator\UuidGeneratorInterface;
use CultuurNet\UDB3\CalendarInterface;
use CultuurNet\UDB3\Event\EventType;
use CultuurNet\UDB3\Event\PlaceEditingServiceInterface;
use CultuurNet\UDB3\EventServiceInterface;
use CultuurNet\UDB3\Location;
use CultuurNet\UDB3\PlaceServiceInterface;
use CultuurNet\UDB3\Theme;
use Drupal\views\Plugin\views\area\Title;

class DefaultEventEditingService implements PlaceEditingServiceInterface
{

    use \CultuurNet\UDB3\OfferEditingTrait;

    /**
     * @var PlaceServiceInterface
     */
    protected $placeService;

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
     * @param EventServiceInterface $eventService
     * @param CommandBusInterface $commandBus
     * @param UuidGeneratorInterface $uuidGenerator
     */
    public function __construct(
        PlaceServiceInterface $placeService,
        CommandBusInterface $commandBus,
        UuidGeneratorInterface $uuidGenerator,
        RepositoryInterface $placeRepository
    ) {
        $this->placeService = $placeService;
        $this->commandBus = $commandBus;
        $this->uuidGenerator = $uuidGenerator;
        $this->placeRepository = $placeRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function createPlace(Title $title, EventType $eventType, Theme $theme, Location $location, CalendarInterface $calendar)
    {
    }


}
