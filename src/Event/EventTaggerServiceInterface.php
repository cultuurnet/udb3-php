<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Event;

use CultuurNet\UDB3\EventNotFoundException;

/**
 * Interface for an event tagger service.
 */
interface EventTaggerServiceInterface
{
    /**
     * @param $eventIds string[]
     * @param $keyword string
     * @return string command id
     * @throws EventNotFoundException
     * @throws \InvalidArgumentException
     */
    public function tagEventsById($eventIds, $keyword);
}
