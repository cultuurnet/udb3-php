<?php

/**
 * @file
 * Contains \Cultuurnet\UDB3\UDB2\EntityRepository.
 */

namespace CultuurNet\UDB3\UDB2;

use Broadway\Domain\AggregateRoot;
use Broadway\Domain\DomainEventStream;
use Broadway\EventSourcing\EventStreamDecoratorInterface;
use Broadway\Repository\RepositoryInterface;

/**
 * Repository decorator that first updates UDB2.
 *
 * When a failure on UDB2 occurs, the whole transaction will fail.
 */
abstract class EntityRepository implements RepositoryInterface
{
    /**
     * @var RepositoryInterface
     */
    protected $decoratee;

    /**
     * @var EntryAPIImprovedFactoryInterface
     */
    protected $entryAPIImprovedFactory;

    /**
     * @var boolean
     */
    protected $syncBack = false;

    /**
     * @var EventStreamDecoratorInterface[]
     */
    private $eventStreamDecorators = array();

    public function __construct(
        RepositoryInterface $decoratee,
        EntryAPIImprovedFactoryInterface $entryAPIImprovedFactory,
        array $eventStreamDecorators = array()
    ) {
        $this->decoratee = $decoratee;
        $this->entryAPIImprovedFactory = $entryAPIImprovedFactory;
        $this->eventStreamDecorators = $eventStreamDecorators;
    }

    public function syncBackOn()
    {
        $this->syncBack = true;
    }

    public function syncBackOff()
    {
        $this->syncBack = false;
    }

    /**
     * {@inheritdoc}
     */
    public function save(AggregateRoot $aggregate)
    {
        if ($this->syncBack) {
            // We can not directly act on the aggregate, as the uncommitted events will
            // be reset once we retrieve them, therefore we clone the object.
            $double = clone $aggregate;
            $domainEventStream = $double->getUncommittedEvents();
            $this->decorateForWrite(
                $aggregate,
                $domainEventStream
            );
        }

        $this->decoratee->save($aggregate);
    }

    /**
     * Decorates for write.
     *
     * @param AggregateRoot $aggregate
     *  The aggregate.
     *
     * @param DomainEventStream $eventStream
     *   The event stream.
     *
     * @return DomainEventStream
     *   The domain event stream.
     */
    private function decorateForWrite(
        AggregateRoot $aggregate,
        DomainEventStream $eventStream
    ) {
        $aggregateType = $this->getType();
        $aggregateIdentifier = $aggregate->getAggregateRootId();

        foreach ($this->eventStreamDecorators as $eventStreamDecorator) {
            $eventStream = $eventStreamDecorator->decorateForWrite(
                $aggregateType,
                $aggregateIdentifier,
                $eventStream
            );
        }

        return $eventStream;
    }

    /**
     * {@inheritdoc}
     */
    abstract public function load($id);

    abstract protected function getType();
}
