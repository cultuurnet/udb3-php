<?php

namespace CultuurNet\UDB3\CommandHandling;

use Broadway\CommandHandling\CommandHandlerInterface;
use Broadway\Domain\DomainEventStream;
use Broadway\Domain\DomainMessage;
use Broadway\Domain\Metadata;
use Broadway\EventStore\TraceableEventStore;
use PHPUnit\Framework\TestCase;

class Scenario
{
    private $eventStore;
    private $commandHandler;
    private $testCase;
    private $aggregateId;

    public function __construct(
        TestCase $testCase,
        TraceableEventStore $eventStore,
        CommandHandlerInterface $commandHandler
    ) {
        $this->testCase       = $testCase;
        $this->eventStore     = $eventStore;
        $this->commandHandler = $commandHandler;
        $this->aggregateId    = 1;
    }

    /**
     * @param  string $aggregateId
     * @return Scenario
     */
    public function withAggregateId($aggregateId)
    {
        $this->aggregateId = $aggregateId;

        return $this;
    }

    /**
     * @param array $events
     *
     * @return Scenario
     */
    public function given(array $events = null)
    {
        if ($events === null) {
            return $this;
        }

        $messages = [];
        $playhead = -1;
        foreach ($events as $event) {
            $playhead++;
            $messages[] = DomainMessage::recordNow($this->aggregateId, $playhead, new Metadata([]), $event);
        }

        $this->eventStore->append($this->aggregateId, new DomainEventStream($messages));

        return $this;
    }

    /**
     * @param mixed $command
     *
     * @return Scenario
     */
    public function when($command)
    {
        $this->eventStore->trace();

        $this->commandHandler->handle($command);

        return $this;
    }

    /**
     * @param array $events
     *
     * @return Scenario
     */
    public function then(array $events)
    {
        $this->testCase->assertEquals($events, $this->eventStore->getEvents());

        $this->eventStore->clearEvents();

        return $this;
    }
}
