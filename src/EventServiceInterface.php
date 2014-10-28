<?php
/**
 * @file
 */

namespace CultuurNet\UDB3;

/**
 * Interface for a service performing event related tasks.
 */
interface EventServiceInterface {

    /**
     * Get a single event by its id.
     *
     * @param string $id
     *   A string uniquely identifying an event.
     *
     * @return array
     *   An event array.
     */
    public function getEvent($id);
} 
