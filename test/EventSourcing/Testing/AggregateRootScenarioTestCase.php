<?php

namespace CultuurNet\UDB3\EventSourcing\Testing;

use Broadway\EventSourcing\AggregateFactory\PublicConstructorAggregateFactory;
use PHPUnit\Framework\TestCase;

/**
 * Base test case that can be used to set up a command handler scenario.
 */
abstract class AggregateRootScenarioTestCase extends TestCase
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
        $aggregateRootClass = $this->getAggregateRootClass();
        $factory            = $this->getAggregateRootFactory();

        return new Scenario($this, $factory, $aggregateRootClass);
    }

    /**
     * Returns a string representing the aggregate root
     *
     * @return string AggregateRoot
     */
    abstract protected function getAggregateRootClass();

    /**
     * Returns a factory for instantiating an aggregate
     *
     * @return \Broadway\EventSourcing\AggregateFactory\AggregateFactoryInterface $factory
     */
    protected function getAggregateRootFactory()
    {
        return new PublicConstructorAggregateFactory();
    }
}
