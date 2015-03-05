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
     * @param string $description
     */
    public function __construct($id, $description)
    {
        $this->id = $id;
        $this->description = $description;
    }
}
