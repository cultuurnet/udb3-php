<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Event;

use CultuurNet\UDB3\EventNotFoundException;
use CultuurNet\UDB3\Keyword;

/**
 * Interface for an event tagger service.
 */
interface EventTaggerServiceInterface
{
    /**
     * @param string[] $eventIds
     * @param Keyword $keyword
     * @return string command id
     * @throws EventNotFoundException
     * @throws \InvalidArgumentException
     */
    public function tagEventsById($eventIds, Keyword $keyword);
}
