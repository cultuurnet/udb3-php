<?php

/**
 * @file
 * Contains CultuurNet\UDB3\Place\Commands\UpdateImage.
 */

namespace CultuurNet\UDB3\Event\Commands;

use CultuurNet\UDB3\MediaObject;

/**
 * Provides a command to update an image of the place.
 */
class UpdateImage
{

    use \CultuurNet\UDB3\UpdateImageTrait;

    /**
     * @param string $id
     * @param int $indexToUpdate
     * @param MediaObject $mediaObject
     */
    public function __construct($id, $indexToUpdate, MediaObject $mediaObject)
    {
        $this->id = $id;
        $this->indexToUpdate = $indexToUpdate;
        $this->mediaObject = $mediaObject;
    }
}