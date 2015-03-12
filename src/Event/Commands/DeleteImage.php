<?php

/**
 * @file
 * Contains CultuurNet\UDB3\Place\Commands\DeleteImage.
 */

namespace CultuurNet\UDB3\Event\Commands;

/**
 * Provides a command to delete an image of the event.
 */
class DeleteImage
{

    use \CultuurNet\UDB3\DeleteImageTrait;

    /**
     * @param string $id
     * @param int $indexToDelete
     */
    public function __construct($id, $indexToDelete)
    {
        $this->id = $id;
        $this->indexToDelete = $indexToDelete;
    }
}
