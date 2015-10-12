<?php

/**
 * @file
 * Contains CultuurNet\UDB3\Place\Commands\DeletePlace.
 */

namespace CultuurNet\UDB3\Place\Commands;

/**
 * Provides a command to delete an place.
 */
class DeletePlace
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
