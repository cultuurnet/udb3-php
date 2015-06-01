<?php

/**
 * @file
 * Contains CultuurNet\UDB3\Place\Commands\DeleteOrganizer.
 */

namespace CultuurNet\UDB3\Place\Commands;

/**
 * Provides a command to delete the organizer of a place.
 */
class DeleteOrganizer
{

    use \CultuurNet\UDB3\DeleteOrganizerTrait;

    /**
     * @param string $id
     * @param string $organizerId
     */
    public function __construct($id, $organizerId)
    {
        $this->id = $id;
        $this->organizerId = $organizerId;
    }
}
