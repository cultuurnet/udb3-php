<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Event;

use Broadway\CommandHandling\CommandBusInterface;
use CultuurNet\UDB3\Calendar;
use CultuurNet\UDB3\Event\ReadModel\DocumentGoneException;
use CultuurNet\UDB3\Event\ReadModel\DocumentRepositoryInterface;
use CultuurNet\UDB3\EventNotFoundException;
use CultuurNet\UDB3\EventServiceInterface;
use CultuurNet\UDB3\Label;
use CultuurNet\UDB3\Language;
use Broadway\UuidGenerator\UuidGeneratorInterface;
use Broadway\Repository\RepositoryInterface;
use CultuurNet\UDB3\Location;
use CultuurNet\UDB3\Offer\Commands\OfferCommandFactoryInterface;
use CultuurNet\UDB3\PlaceService;
use CultuurNet\UDB3\Title;

class DefaultEventEditingServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DefaultEventEditingService
     */
    protected $eventEditingService;

    /**
     * @var EventServiceInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $eventService;

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
        $this->eventService = $this->getMock(EventServiceInterface::class);

        $this->commandBus = $this->getMock(CommandBusInterface::class);

        $this->uuidGenerator = $this->getMock(
            UuidGeneratorInterface::class
        );

        $this->commandFactory = $this->getMock(OfferCommandFactoryInterface::class);

        /** @var DocumentRepositoryInterface $repository */
        $this->readRepository = $this->getMock(DocumentRepositoryInterface::class);
        /** @var PlaceService $placeService */
        $placeService = $this->getMock(
            PlaceService::class,
            array(),
            array(),
            '',
            false
        );

        $this->writeRepository = $this->getMock(RepositoryInterface::class);

        $this->eventEditingService = new DefaultEventEditingService(
            $this->eventService,
            $this->commandBus,
            $this->uuidGenerator,
            $this->readRepository,
            $placeService,
            $this->commandFactory,
            $this->writeRepository
        );
    }

    /**
     * @test
     */
    public function it_refuses_to_translate_title_of_unknown_event()
    {
        $id = 'some-unknown-id';

        $this->setExpectedException(DocumentGoneException::class);

        $this->setUpEventNotFound($id);

        $this->eventEditingService->translateTitle(
            $id,
            new Language('nl'),
            'new title'
        );
    }

    /**
     * @test
     */
    public function it_refuses_to_translate_description_of_unknown_event()
    {
        $id = 'some-unknown-id';

        $this->setExpectedException(DocumentGoneException::class);

        $this->setUpEventNotFound($id);

        $this->eventEditingService->translateDescription(
            $id,
            new Language('nl'),
            'new description'
        );
    }

    /**
     * @test
     */
    public function it_refuses_to_label_an_unknown_event()
    {
        $id = 'some-unknown-id';

        $this->setExpectedException(DocumentGoneException::class);

        $this->setUpEventNotFound($id);

        $this->eventEditingService->addLabel($id, new Label('foo'));
    }

    /**
     * @test
     */
    public function it_refuses_to_remove_a_label_from_an_unknown_event()
    {
        $id = 'some-unknown-id';

        $this->setExpectedException(DocumentGoneException::class);

        $this->setUpEventNotFound($id);

        $this->eventEditingService->deleteLabel($id, new Label('foo'));
    }

    /**
     * @test
     */
    public function it_can_create_a_new_event()
    {
        $eventId = 'generated-uuid';
        $title = new Title('Title');
        $eventType = new EventType('0.50.4.0.0', 'concert');
        $location = new Location('LOCATION-ABC-123', '$name', '$country', '$locality', '$postalcode', '$street');
        $calendar = new Calendar('permanent', '', '');
        $theme = null;

        $event = Event::create($eventId, $title, $eventType, $location, $calendar, $theme);

        $this->uuidGenerator->expects($this->once())
            ->method('generate')
            ->willReturn('generated-uuid');

        $this->writeRepository->expects($this->once())
            ->method('save')
            ->with($event);

        $this->eventEditingService->createEvent($title, $eventType, $location, $calendar, $theme);
    }

    private function setUpEventNotFound($id)
    {
        $this->readRepository->expects($this->once())
            ->method('get')
            ->with($id)
            ->willThrowException(new DocumentGoneException());
    }
}
