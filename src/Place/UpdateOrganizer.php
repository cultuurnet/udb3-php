<?php

/**
 * @file
 * Contains CultuurNet\UDB3\Place\UpdateDescription.
 */

namespace CultuurNet\UDB3\Place;

/**
 * Provides a command to update the event description for the main language.
 */
class updateOrganizer {

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
