<?php

/**
 * @file
 * Contains CultuurNet\UDB3\Event\Commands\DeleteEvent.
 */

namespace CultuurNet\UDB3\Event\Commands;

/**
 * Provides a command to delete an event.
 */
class DeleteEvent
{

    /**
     * @var string
     */
    private $id;

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
