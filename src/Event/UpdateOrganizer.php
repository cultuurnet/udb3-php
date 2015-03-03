<?php

/**
 * @file
 * Contains CultuurNet\UDB3\Event\UpdateDescription.
 */

namespace CultuurNet\UDB3\Event;

/**
 * Provides a command to update the event description for the main language.
 */
class UpdateOrganizer
{

    use \CultuurNet\UDB3\UpdateOrganizerTrait;

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
