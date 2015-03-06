<?php

/**
 * @file
 * Contains CultuurNet\UDB3\Event\Commands\DeleteOrganizer.
 */

namespace CultuurNet\UDB3\Event\Commands;

/**
 * Provides a command to delete the organizer of an event.
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
