<?php

/**
 * @file
 * Contains \Cultuurnet\UDB3\PlaceServiceInterface.
 */

namespace CultuurNet\UDB3;

/**
 * Interface for a service performing place related tasks.
 */
interface PlaceServiceInterface
{
    /**
     * Get a single place by its id.
     *
     * @param string $id
     *   A string uniquely identifying a place.
     *
     * @return array
     *   A place array.
     *
     * @throws PlaceNotFoundException if an event can not be found for the given id
     */
    public function getPlace($id);

}
