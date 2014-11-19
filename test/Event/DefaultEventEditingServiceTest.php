<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Event;


use Broadway\CommandHandling\CommandBusInterface;
use CultuurNet\UDB3\EventNotFoundException;
use CultuurNet\UDB3\EventServiceInterface;
use CultuurNet\UDB3\Language;

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

    public function setUp()
    {
        $this->eventService = $this->getMock(
            'CultuurNet\\UDB3\\EventServiceInterface'
        );

        $this->commandBus = $this->getMock(
            'Broadway\\CommandHandling\\CommandBusInterface'
        );

        $this->eventEditingService = new DefaultEventEditingService(
            $this->eventService,
            $this->commandBus
        );
    }

    /**
     * @test
     */
    public function it_refuses_to_translate_title_of_unknown_event()
    {
        $id = 'some-unknown-id';

        $this->setExpectedException('CultuurNet\\UDB3\\EventNotFoundException');

        $this->eventService->expects($this->once())
            ->method('getEvent')
            ->with($id)
            ->willThrowException(new EventNotFoundException());

        $this->eventEditingService->translateTitle($id, new Language('nl'), 'new title');
    }

    /**
     * @test
     */
    public function it_refuses_to_translate_description_of_unknown_event()
    {
        $id = 'some-unknown-id';

        $this->setExpectedException('CultuurNet\\UDB3\\EventNotFoundException');

        $this->eventService->expects($this->once())
            ->method('getEvent')
            ->with($id)
            ->willThrowException(new EventNotFoundException());

        $this->eventEditingService->translateDescription($id, new Language('nl'), 'new description');
    }
} 
