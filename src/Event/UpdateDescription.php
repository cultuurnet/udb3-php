<?php

/**
 * @file
 * Contains CultuurNet\UDB3\Event\UpdateDescription.
 */

namespace CultuurNet\UDB3\Event;

/**
 * Provides a command to update the event description for the main language.
 */
class UpdateDescription {

    use \CultuurNet\UDB3\UpdateDescriptionTrait;

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
