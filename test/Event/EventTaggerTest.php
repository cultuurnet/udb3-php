<?php

namespace CultuurNet\UDB3\Event;

use Broadway\CommandHandling\Testing\CommandHandlerScenarioTestCase;
use Broadway\EventStore\EventStoreInterface;
use Broadway\EventHandling\EventBusInterface;
use CultuurNet\UDB3\Event\TagEvents;
use CultuurNet\UDB3\Event\EventWasTagged;

class EventTaggerTest extends CommandHandlerScenarioTestCase
{
    protected function createCommandHandler(EventStoreInterface $eventStore, EventBusInterface $eventBus)
    {
        $repository = new EventRepository($eventStore, $eventBus);

        return new EventTagger($repository);
    }

    /**
     * @test
     */
    public function it_can_tag_a_list_of_events_with_a_keyword()
    {
        $ids = ['eventId1', 'eventId2'];
        $keyword = 'awesome';

        $this->scenario
          ->given([])
          ->when(new TagEvents($ids, $keyword))
          ->then([
            new EventWasTagged($ids[0], $keyword),
            new EventWasTagged($ids[1], $keyword)
          ]);
    }
} 