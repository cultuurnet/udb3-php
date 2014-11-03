<?php

namespace CultuurNet\UDB3\Tagging;

use Broadway\CommandHandling\Testing\CommandHandlerScenarioTestCase;
use Broadway\EventStore\EventStoreInterface;
use Broadway\EventHandling\EventBusInterface;
use CultuurNet\UDB3\TagEventsCommand;
use CultuurNet\UDB3\TagEventEvent;

class TagEventsCommandHandlerTest extends CommandHandlerScenarioTestCase
{
    protected function createCommandHandler(EventStoreInterface $eventStore, EventBusInterface $eventBus)
    {
        $repository = new TaggingRepository($eventStore, $eventBus);

        return new TagEventsCommandHandler($repository);
    }

    /**
     * @test
     */
    public function it_can_tag_a_list_of_events_with_a_tag()
    {
        $ids = ['eventId1', 'eventId2'];
        $tag = 'awesome';

        $this->scenario
          ->given([])
          ->when(new TagEventsCommand($ids, $tag))
          ->then([
            new TagEventEvent($ids[0], $tag),
            new TagEventEvent($ids[1], $tag)
          ]);
    }
} 