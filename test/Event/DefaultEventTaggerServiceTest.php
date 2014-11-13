<?php


namespace CultuurNet\UDB3\Event;


use Broadway\CommandHandling\CommandBusInterface;
use CultuurNet\UDB3\EventNotFoundException;
use CultuurNet\UDB3\EventServiceInterface;
use CultuurNet\UDB3\Keyword;

class DefaultEventTaggerServiceTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var DefaultEventTaggerService
     */
    protected $eventTagger;

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
        $this->eventService = $this->getMock(
            'CultuurNet\\UDB3\\EventServiceInterface'
        );

        $this->commandBus = $this->getMock(
            'Broadway\\CommandHandling\\CommandBusInterface'
        );

        $this->eventTagger = new DefaultEventTaggerService(
            $this->eventService,
            $this->commandBus
        );
    }

    /**
     * @test
     */
    public function it_dispatches_a_tag_command_for_a_single_id()
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
                    new TagEvents(
                        array('event1'),
                        new Keyword('some-keyword')
                    )
                ));

        $this->eventTagger->tagEventsById($eventIds, new Keyword('some-keyword'));
    }

    /**
     * @test
     */
    public function it_dispatches_a_tag_command_for_multiple_ids()
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
                    new TagEvents(
                        array('event1', 'event2'),
                        new Keyword('some-keyword')
                    )
                )
            );

        $this->eventTagger->tagEventsById($eventIds, new Keyword('some-keyword'));
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

        $this->setExpectedException('CultuurNet\\UDB3\\EventNotFoundException');

        $this->eventTagger->tagEventsById($eventIds, new Keyword('some-keyword'));
    }

    /**
     * @test
     */
    public function it_does_not_dispatch_a_command_when_no_ids_are_provided()
    {
        $eventIds = [];

        $this->setExpectedException('InvalidArgumentException', 'no event Ids to tag');

        $this->commandBus->expects($this->never())
            ->method('dispatch');

        $this->eventTagger->tagEventsById($eventIds, new Keyword('some-keyword'));
    }
}
