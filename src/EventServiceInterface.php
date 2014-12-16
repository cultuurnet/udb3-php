<?php
/**
 * @file
 */

namespace CultuurNet\UDB3;

/**
 * Interface for a service performing event related tasks.
 */
interface EventServiceInterface
{
    /**
     * Get a single event by its id.
     *
     * @param string $id
     *   A string uniquely identifying an event.
     *
     * @return array
     *   An event array.
     *
     * @throws EventNotFoundException if an event can not be found for the given id
     */
    public function getEvent($id);

    /**
     * @param string $organizerId
     * @return string[]
     */
    public function eventsOrganizedByOrganizer($organizerId);

    /**
     * @param string $placeId
     * @return string[] mixed
     */
    public function eventsLocatedAtPlace($placeId);
}
