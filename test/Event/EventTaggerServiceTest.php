<?php


namespace CultuurNet\UDB3\Event;


use Broadway\CommandHandling\SimpleCommandBus;
use CultuurNet\UDB3\EventNotFoundException;
use CultuurNet\UDB3\EventServiceInterface;

class EventTaggerServiceTest extends \PHPUnit_Framework_TestCase {

    /**
     * @var EventTaggerService
     */
    protected $eventTagger;

    /**
     * @var EventServiceInterface
     */
    protected $eventService;

    /**
     * @var SimpleCommandBus
     */
    protected $commandBus;

    public function setUp()
    {
        $this->eventService = $this->getMock('CultuurNet\UDB3\EventServiceInterface', array('getEvent'));
        $this->commandBus = $this->getMock('Broadway\CommandHandling\SimpleCommandBus', array('dispatch'));

        $this->eventTagger = new EventTaggerService($this->eventService, $this->commandBus);
    }

    /**
     * @test
     */
    public function it_dispatches_a_tag_command_for_a_single_id ()
    {
        $eventIds = [
            'event1'
        ];

        $this->eventService->expects($this->once())
            ->method('getEvent')
            ->with($this->equalTo('event1'));

        $this->commandBus->expects($this->once())
            ->method('dispatch');

        $this->eventTagger->tagEventsById($eventIds, 'some-keyword');
    }

    /**
     * @test
     */
    public function it_dispatches_a_tag_command_for_multiple_ids ()
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
            ->method('dispatch');

        $this->eventTagger->tagEventsById($eventIds, 'some-keyword');
    }

    /**
     * @test
     */
    public function it_does_not_tag_empty_keywords () {
        $eventIds = [
            'event1'
        ];

        $this->setExpectedException('Exception', 'invalid keyword');

        $this->commandBus->expects($this->never())
            ->method('dispatch');

        $this->eventTagger->tagEventsById($eventIds, '');
    }

    /**
     * @test
     */
    public function it_does_not_dispatch_a_command_when_an_event_is_not_found () {
        $eventIds = [
            'event1',
        ];

        $this->eventService->expects($this->once())
            ->method('getEvent')
            ->will($this->throwException(new EventNotFoundException));

        $this->commandBus->expects($this->never())
            ->method('dispatch');

        $this->setExpectedException('CultuurNet\UDB3\EventNotFoundException');

        $this->eventTagger->tagEventsById($eventIds, 'some-keyword');
    }

    /**
     * @test
     */
    public function it_does_not_dispatch_a_command_when_no_ids_are_provided () {
        $eventIds = [];

        $this->setExpectedException('Exception', 'no event Ids to tag');

        $this->commandBus->expects($this->never())
            ->method('dispatch');

        $this->eventTagger->tagEventsById($eventIds, 'some-keyword');
    }
}
 