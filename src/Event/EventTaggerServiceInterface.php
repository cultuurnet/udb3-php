<?php


namespace CultuurNet\UDB3\Event;

/**
 * Interface for an event tagger service.
 */
interface EventTaggerServiceInterface {

    /**
     * @param $eventIds string[]
     * @param $keyword string
     * @throws EventNotFoundException
     * @throws \Exception
     */
    public function tagEventsById($eventIds, $keyword);

} 