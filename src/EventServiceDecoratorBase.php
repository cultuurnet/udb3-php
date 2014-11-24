<?php
/**
 * @file
 */

namespace CultuurNet\UDB3;

/**
 * Base class for EventServiceInterface decorators.
 */
abstract class EventServiceDecoratorBase implements EventServiceInterface
{
    /**
     * @var EventServiceInterface
     */
    protected $decoratee;

    /**
     * Construct a new decorator.
     *
     * @param EventServiceInterface $decoratee
     *   The EventServiceInterface to decorate.
     */
    public function __construct(EventServiceInterface $decoratee)
    {
        $this->decoratee = $decoratee;
    }

    /**
     * {@inheritdoc}
     */
    public function getEvent($id)
    {
        return $this->decoratee->getEvent($id);
    }
}
