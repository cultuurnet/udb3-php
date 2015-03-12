<?php

/**
 * @file
 * Contains CultuurNet\UDB3\Event\Commands\AddImage.
 */

namespace CultuurNet\UDB3\Event\Commands;

use CultuurNet\UDB3\MediaObject;

/**
 * Provides a command to add an image to the event.
 */
class AddImage
{

    use \CultuurNet\UDB3\AddImageTrait;

    /**
     * @param string $id
     * @param MediaObject $mediaObject
     */
    public function __construct($id, MediaObject $mediaObject)
    {
        $this->id = $id;
        $this->mediaObject = $mediaObject;
    }
}