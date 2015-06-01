<?php

namespace CultuurNet\UDB3\Place;

use Broadway\CommandHandling\Testing\CommandHandlerScenarioTestCase;
use Broadway\EventHandling\EventBusInterface;
use Broadway\EventStore\EventStoreInterface;
use CultuurNet\UDB3\Address;
use CultuurNet\UDB3\Calendar;
use CultuurNet\UDB3\Event\EventType;
use CultuurNet\UDB3\Place\CommandHandler;
use CultuurNet\UDB3\Place\Commands\DeletePlace;
use CultuurNet\UDB3\Place\Events\PlaceCreated;
use CultuurNet\UDB3\Place\Events\PlaceDeleted;
use CultuurNet\UDB3\Search\SearchServiceInterface;
use CultuurNet\UDB3\Title;
use PHPUnit_Framework_MockObject_MockObject;

class PlaceHandlerTest extends CommandHandlerScenarioTestCase
{
    /**
     * @var SearchServiceInterface|PHPUnit_Framework_MockObject_MockObject
     */
    protected $search;

    public function setUp()
    {
        $this->search = $this->getMock(SearchServiceInterface::class);

        parent::setUp();
    }

    protected function createCommandHandler(
        EventStoreInterface $eventStore,
        EventBusInterface $eventBus
    ) {
        $repository = new PlaceRepository(
            $eventStore,
            $eventBus
        );

        return new CommandHandler($repository, $this->search);
    }

    private function factorPlaceCreated($id)
    {
        return new PlaceCreated(
            $id,
            new Title('some representative title'),
            new EventType('0.50.4.0.0', 'concert'),
            new Address('LOCATION-ABC-123', '$name', '$country', '$locality', '$postalcode', '$street'),
            new Calendar('permanent', '', '')
        );
    }

    /**
     * @test
     */
    public function it_can_delete_places()
    {
        $id = '1';
        $this->scenario
            ->withAggregateId($id)
            ->given(
                [$this->factorPlaceCreated($id)]
            )
            ->when(
              new DeletePlace($id))
            ->then([new PlaceDeleted($id)]);
    }
}
