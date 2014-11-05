<?php

namespace CultuurNet\UDB3\Event;

use Broadway\CommandHandling\Testing\CommandHandlerScenarioTestCase;
use Broadway\EventStore\EventStoreInterface;
use Broadway\EventHandling\EventBusInterface;

class EventTaggerTest extends CommandHandlerScenarioTestCase
{
    protected function createCommandHandler(
        EventStoreInterface $eventStore,
        EventBusInterface $eventBus
    ) {
        $repository = new EventRepository(
            $eventStore,
            $eventBus,
            $this->getMock('\\CultuurNet\\UDB3\\SearchAPI2\\SearchServiceInterface')
        );

        return new EventCommandHandler($repository);
    }

    /**
     * @test
     */
    public function it_can_tag_a_list_of_events_with_a_keyword()
    {
        $ids = ['eventId1', 'eventId2'];
        $keyword = 'awesome';

        $this->scenario
            ->withAggregateId($ids[0])
            ->given(
                [
                    new EventCreated($ids[0])
                ]
            )
            ->withAggregateId($ids[1])
            ->given(
                [
                    new EventCreated($ids[1])
                ]
            )
            ->when(new TagEvents($ids, $keyword))
            ->then(
                [
                    new EventWasTagged($ids[0], $keyword),
                    new EventWasTagged($ids[1], $keyword)
                ]
            );
    }
}
