<?php

namespace CultuurNet\UDB3;

use CultuurNet\UDB3\Event\EventServiceInterface;

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

    /**
     * @param string $organizerId
     * @return string[]
     */
    public function eventsOrganizedByOrganizer($organizerId)
    {
        return $this->decoratee->eventsOrganizedByOrganizer($organizerId);
    }

    /**
     * @param string $placeId
     * @return string[] mixed
     */
    public function eventsLocatedAtPlace($placeId)
    {
        return $this->decoratee->eventsLocatedAtPlace($placeId);
    }
}
