<?php


namespace CultuurNet\UDB3\Event;

use Broadway\CommandHandling\CommandBusInterface;
use CultuurNet\UDB3\EventNotFoundException;
use CultuurNet\UDB3\EventServiceInterface;
use CultuurNet\UDB3\Label;

class DefaultEventLabellerServiceTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var DefaultEventLabellerService
     */
    protected $eventLabeller;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|EventServiceInterface
     */
    protected $eventService;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|CommandBusInterface
     */
    protected $commandBus;

    public function setUp()
    {
        $this->eventService = $this->getMock(EventServiceInterface::class);

        $this->commandBus = $this->getMock(CommandBusInterface::class);

        $this->eventLabeller = new DefaultEventLabellerService(
            $this->eventService,
            $this->commandBus
        );
    }

    /**
     * @test
     */
    public function it_dispatches_a_label_command_for_a_single_id()
    {
        $eventIds = [
            'event1'
        ];

        $this->eventService->expects($this->once())
            ->method('getEvent')
            ->with($this->equalTo('event1'));

        $this->commandBus->expects($this->once())
            ->method('dispatch')
            ->with(
                $this->equalTo(
                    new LabelEvents(
                        array('event1'),
                        new Label('some-label')
                    )
                )
            );

        $this->eventLabeller->labelEventsById(
            $eventIds,
            new Label('some-label')
        );
    }

    /**
     * @test
     */
    public function it_dispatches_a_label_command_for_multiple_ids()
    {
        $eventIds = [
            'event1',
            'event2'
        ];

        $this->eventService->expects($this->exactly(2))
            ->method('getEvent')
            ->withConsecutive(
                $this->equalTo('event1'),
                $this->equalTo('event2')
            );

        $this->commandBus->expects($this->once())
            ->method('dispatch')
            ->with(
                $this->equalTo(
                    new LabelEvents(
                        array('event1', 'event2'),
                        new Label('some-label')
                    )
                )
            );

        $this->eventLabeller->labelEventsById(
            $eventIds,
            new Label('some-label')
        );
    }

    /**
     * @test
     */
    public function it_does_not_dispatch_a_command_when_an_event_is_not_found()
    {
        $eventIds = [
            'event1',
        ];

        $this->eventService->expects($this->once())
            ->method('getEvent')
            ->will($this->throwException(new EventNotFoundException));

        $this->commandBus->expects($this->never())
            ->method('dispatch');

        $this->setExpectedException(EventNotFoundException::class);

        $this->eventLabeller->labelEventsById(
            $eventIds,
            new Label('some-label')
        );
    }

    /**
     * @test
     */
    public function it_does_not_dispatch_a_command_when_no_ids_are_provided()
    {
        $eventIds = [];

        $this->setExpectedException(
            'InvalidArgumentException',
            'no event Ids to label'
        );

        $this->commandBus->expects($this->never())
            ->method('dispatch');

        $this->eventLabeller->labelEventsById(
            $eventIds,
            new Label('some-label')
        );
    }
}
