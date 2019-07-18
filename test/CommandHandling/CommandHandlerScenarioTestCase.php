<?php

namespace CultuurNet\UDB3\CommandHandling;


use Broadway\CommandHandling\CommandHandlerInterface;
use Broadway\EventHandling\EventBusInterface;
use Broadway\EventHandling\SimpleEventBus;
use Broadway\EventStore\EventStoreInterface;
use Broadway\EventStore\InMemoryEventStore;
use Broadway\EventStore\TraceableEventStore;
use PHPUnit\Framework\TestCase;

abstract class CommandHandlerScenarioTestCase extends TestCase
{
    /**
     * @var Scenario
     */
    protected $scenario;

    public function setUp()
    {
        $this->scenario = $this->createScenario();
    }

    /**
     * @return Scenario
     */
    protected function createScenario()
    {
        $eventStore = new TraceableEventStore(new InMemoryEventStore());
        $eventBus = new SimpleEventBus();
        $commandHandler = $this->createCommandHandler($eventStore, $eventBus);

        return new Scenario($this, $eventStore, $commandHandler);
    }

    /**
     * Create a command handler for the given scenario test case.
     *
     * @param EventStoreInterface $eventStore
     * @param EventBusInterface $eventBus
     *
     * @return CommandHandlerInterface
     */
    abstract protected function createCommandHandler(EventStoreInterface $eventStore, EventBusInterface $eventBus);
}
