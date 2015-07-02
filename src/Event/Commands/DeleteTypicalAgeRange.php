<?php

/**
 * @file
 * Contains CultuurNet\UDB3\Event\Commands\DeleteTypicalAgeRange.
 */

namespace CultuurNet\UDB3\Event\Commands;

/**
 * Provides a command to delete the typical age range of the event.
 */
class DeleteTypicalAgeRange
{

    /**
     * @param string $id
     */
    public function __construct($id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }
}
