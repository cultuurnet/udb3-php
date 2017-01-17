<?php

namespace CultuurNet\UDB3\EventSourcing;

use Broadway\Domain\DateTime;
use Broadway\Domain\DomainEventStream;
use Broadway\Domain\DomainMessage;
use Broadway\Domain\Metadata;
use Broadway\EventStore\EventStoreInterface;

class CopyAwareEventStoreDecoratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var EventStoreInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $eventStore;

    /**
     * @var CopyAwareEventStoreDecorator
     */
    protected $copyAwareEventStore;

    protected function setUp()
    {
        $this->eventStore = $this->createMock(EventStoreInterface::class);
        $this->copyAwareEventStore = new CopyAwareEventStoreDecorator($this->eventStore);
    }

    /**
     * @test
     */
    public function it_should_return_the_aggregate_event_stream_when_it_contains_all_history()
    {
        $domainMessage = $this->getDomainMessage(0, '');
        $expectedEventStream = new DomainEventStream([$domainMessage]);

        $this->eventStore->expects($this->once())
            ->method('load')
            ->with('94ae3a8f-596a-480b-b4f0-be7f8fe7e9b3')
            ->willReturn(new DomainEventStream([$domainMessage]));

        $eventStream = $this->copyAwareEventStore->load('94ae3a8f-596a-480b-b4f0-be7f8fe7e9b3');

        $this->assertEquals($expectedEventStream, $eventStream);
    }

    /**
     * @test
     */
    public function it_should_load_the_parent_history_when_aggregate_history_is_incomplete()
    {
        $parentFirstEventMessage = $this->getDomainMessage(0, '');
        $parentOtherEventMessage = $this->getDomainMessage(1, '');
        $parentOldestEventMessage = $this->getDomainMessage(2, '');
        $aggregateOldestEventMessage = $this->getDomainMessage(2, '94ae3a8f-596a-480b-b4f0-be7f8fe7e9b3');
        $expectedEventStream = new DomainEventStream([
            $parentFirstEventMessage,
            $parentOtherEventMessage,
            $aggregateOldestEventMessage
        ]);

        $this->eventStore->method('load')
            ->will($this->onConsecutiveCalls(
                new DomainEventStream([$aggregateOldestEventMessage]),
                new DomainEventStream([$parentFirstEventMessage, $parentOtherEventMessage, $parentOldestEventMessage])
            ));

        $eventStream = $this->copyAwareEventStore->load('422d7cb7-016c-42ca-a08e-277b3695ba41');

        $this->assertEquals($expectedEventStream, $eventStream);
    }

    public function it_should_only_load_the_inherited_parent_history_when_there_jumps_in_playhead()
    {
        $parentFirstEventMessage = $this->getDomainMessage(0, '');
        $parentJumpedEventMessage = $this->getDomainMessage(2, '');
        $parentOldestEventMessage = $this->getDomainMessage(3, '');
        $aggregateOldestEventMessage = $this->getDomainMessage(4, '94ae3a8f-596a-480b-b4f0-be7f8fe7e9b3');
        $expectedEventStream = new DomainEventStream([
            $parentFirstEventMessage,
            $parentJumpedEventMessage,
            $parentOldestEventMessage,
            $aggregateOldestEventMessage
        ]);

        $this->eventStore->method('load')
            ->will($this->onConsecutiveCalls(
                new DomainEventStream([$aggregateOldestEventMessage]),
                new DomainEventStream([$parentFirstEventMessage, $parentJumpedEventMessage, $parentOldestEventMessage])
            ));

        $eventStream = $this->copyAwareEventStore->load('422d7cb7-016c-42ca-a08e-277b3695ba41');

        $this->assertEquals($expectedEventStream, $eventStream);
    }

    private function getDomainMessage($playhead, $parentId)
    {
        $event = $this->createMock(AggregateCopiedEventInterface::class);
        $event->method('getParentAggregateId')->willReturn($parentId);
        return new DomainMessage('1-2-3', $playhead, new Metadata([]), $event, DateTime::now());
    }
}
