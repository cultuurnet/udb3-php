<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Event;

use Broadway\CommandHandling\CommandBusInterface;
use CultuurNet\UDB3\EventNotFoundException;
use CultuurNet\UDB3\EventServiceInterface;
use CultuurNet\UDB3\Label;
use CultuurNet\UDB3\Language;
use Broadway\UuidGenerator\UuidGeneratorInterface;
use Broadway\Repository\RepositoryInterface;
use CultuurNet\UDB3\PlaceService;

class DefaultEventEditingServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var EventEditingServiceInterface
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
     * @var UuidGeneratorInterface
     */
    protected $uuidGenerator;

    public function setUp()
    {
        $this->eventService = $this->getMock(EventServiceInterface::class);

        $this->commandBus = $this->getMock(CommandBusInterface::class);

        $this->uuidGenerator = $this->getMock(
            UuidGeneratorInterface::class
        );

        $this->eventEditingService = new DefaultEventEditingService(
            $this->eventService,
            $this->commandBus,
            $this->uuidGenerator,
            $this->getMock(RepositoryInterface::class),
            $this->getMock(PlaceService::class, array(), array(), '', false)
        );
    }

    /**
     * @test
     */
    public function it_refuses_to_translate_title_of_unknown_event()
    {
        $id = 'some-unknown-id';

        $this->setExpectedException(EventNotFoundException::class);

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

        $this->setExpectedException(EventNotFoundException::class);

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

        $this->setExpectedException(EventNotFoundException::class);

        $this->setUpEventNotFound($id);

        $this->eventEditingService->label($id, new Label('foo'));
    }

    /**
     * @test
     */
    public function it_refuses_to_remove_a_label_from_an_unknown_event()
    {
        $id = 'some-unknown-id';

        $this->setExpectedException(EventNotFoundException::class);

        $this->setUpEventNotFound($id);

        $this->eventEditingService->unlabel($id, new Label('foo'));
    }

    private function setUpEventNotFound($id)
    {
        $this->eventService->expects($this->once())
            ->method('getEvent')
            ->with($id)
            ->willThrowException(new EventNotFoundException());
    }
}
