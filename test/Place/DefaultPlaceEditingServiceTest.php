<?php

namespace CultuurNet\UDB3\Place;

use Broadway\CommandHandling\CommandBusInterface;
use Broadway\Repository\RepositoryInterface;
use Broadway\UuidGenerator\UuidGeneratorInterface;
use CultuurNet\UDB3\Address;
use CultuurNet\UDB3\Calendar;
use CultuurNet\UDB3\Event\EventType;
use CultuurNet\UDB3\Event\ReadModel\DocumentRepositoryInterface;
use CultuurNet\UDB3\Location;
use CultuurNet\UDB3\Offer\Commands\OfferCommandFactoryInterface;
use CultuurNet\UDB3\Title;

class DefaultPlaceEditingServiceTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var DefaultPlaceEditingService
     */
    protected $placeEditingService;

    /**
     * @var CommandBusInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $commandBus;

    /**
     * @var UuidGeneratorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $uuidGenerator;

    /**
     * @var OfferCommandFactoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $commandFactory;

    /**
     * @var DocumentRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $readRepository;

    /**
     * @var RepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $writeRepository;

    public function setUp()
    {
        $this->commandBus = $this->getMock(CommandBusInterface::class);

        $this->uuidGenerator = $this->getMock(
            UuidGeneratorInterface::class
        );

        $this->commandFactory = $this->getMock(OfferCommandFactoryInterface::class);

        /** @var DocumentRepositoryInterface $repository */
        $this->readRepository = $this->getMock(DocumentRepositoryInterface::class);

        $this->writeRepository = $this->getMock(RepositoryInterface::class);

        $this->placeEditingService = new DefaultPlaceEditingService(
            $this->commandBus,
            $this->uuidGenerator,
            $this->readRepository,
            $this->commandFactory,
            $this->writeRepository
        );
    }

    /**
     * @test
     */
    public function it_can_create_a_new_place()
    {
        $placeId = 'generated-uuid';
        $title = new Title('Title');
        $eventType = new EventType('0.50.4.0.0', 'concert');
        $address = new Address('$street', '$postalcode', '$locality', '$country');
        $calendar = new Calendar('permanent', '', '');
        $theme = null;

        $place = Place::createPlace($placeId, $title, $eventType, $address, $calendar, $theme);

        $this->uuidGenerator->expects($this->once())
            ->method('generate')
            ->willReturn('generated-uuid');

        $this->writeRepository->expects($this->once())
            ->method('save')
            ->with($place);

        $this->placeEditingService->createPlace($title, $eventType, $address, $calendar, $theme);
    }
}
