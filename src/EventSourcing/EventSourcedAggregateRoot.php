<?php

namespace CultuurNet\UDB3\EventSourcing;

use Broadway\EventSourcing\EventSourcedAggregateRoot as OriginalEventSourcedAggregateRoot;
use Broadway\Domain\AggregateRoot as AggregateRootInterface;
use Broadway\Domain\DomainEventStream;
use Broadway\Domain\DomainEventStreamInterface;
use Broadway\Domain\DomainMessage;
use Broadway\Domain\Metadata;
use RuntimeException;

/**
 * Base class for event sourced aggregate roots, with copy functionality.
 *
 * Based on Broadways EventSourcedAggregateRoot. In addition it provides
 * a copy functionality which maintains the state of the object, but resets
 * the playhead. We could not change the playhead in a child class, because it
 * is declared private.
 *
 * Unfortunately we need to extend Broadways EventSourcedAggregateRoot because
 * it is required in the EventSourcingRepository.
 */
abstract class EventSourcedAggregateRoot extends OriginalEventSourcedAggregateRoot implements AggregateRootInterface
{
    /**
     * @var array
     */
    private $uncommittedEvents = array();
    private $playhead = -1; // 0-based playhead allows events[0] to contain playhead 0

    /**
     * Applies an event. The event is added to the AggregateRoot's list of uncommited events.
     *
     * @param $event
     * @internal
     */
    public function apply($event)
    {
        $this->handleRecursively($event);

        $this->playhead++;
        $this->uncommittedEvents[] = DomainMessage::recordNow(
            $this->getAggregateRootId(),
            $this->playhead,
            new Metadata(array()),
            $event
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getUncommittedEvents()
    {
        $stream = new DomainEventStream($this->uncommittedEvents);

        $this->uncommittedEvents = array();

        return $stream;
    }

    /**
     * Initializes the aggregate using the given "history" of events.
     */
    public function initializeState(DomainEventStreamInterface $stream)
    {
        foreach ($stream as $message) {
            $this->playhead++;
            $this->handleRecursively($message->getPayload());
        }
    }

    /**
     * Handles event if capable.
     *
     * @param $event
     */
    protected function handle($event)
    {
        $method = $this->getApplyMethod($event);

        if (! method_exists($this, $method)) {
            return;
        }

        $this->$method($event);
    }

    /**
     * {@inheritDoc}
     */
    protected function handleRecursively($event)
    {
        $this->handle($event);

        foreach ($this->getChildEntities() as $entity) {
            $entity->registerAggregateRoot($this);
            $entity->handleRecursively($event);
        }
    }

    /**
     * Returns all child entities
     *
     * Override this method if your aggregate root contains child entities.
     *
     * @return array
     */
    protected function getChildEntities()
    {
        return array();
    }

    private function getApplyMethod($event)
    {
        $classParts = explode('\\', get_class($event));

        return 'apply' . end($classParts);
    }

    /**
     * Creates a copy with the playhead reset.
     *
     * @throws RuntimeException When there are any uncommitted events.
     *
     * @return static
     */
    protected function copyWithoutHistory()
    {
        if (!empty($this->uncommittedEvents)) {
            throw new RuntimeException('I refuse to copy, there are uncommitted events present.');
        }

        $copy = clone $this;
        $copy->playhead = -1;

        return $copy;
    }
}
