<?php

namespace CultuurNet\UDB3\Event;

use Broadway\EventHandling\EventListenerInterface;
use CultuurNet\UDB3\Event\ReadModel\CacheCalendarRepository;
use CultuurNet\UDB3\EventHandling\DelegateEventHandlingToSpecificMethodTrait;

class EventCalendarProjector implements EventListenerInterface
{
    use DelegateEventHandlingToSpecificMethodTrait;

    /**
     * @var CacheCalendarRepository
     */
    protected $repository;

    /**
     * @param CacheCalendarRepository $repository
     */
    public function __construct(CacheCalendarRepository $repository)
    {
        $this->repository = $repository;
    }
}
