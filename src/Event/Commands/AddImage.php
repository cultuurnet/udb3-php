<?php

/**
 * @file
 * Contains CultuurNet\UDB3\Event\Commands\AddImage.
 */

namespace CultuurNet\UDB3\Event\Commands;

use CultuurNet\UDB3\Media\Image;

/**
 * Provides a command to add an image to the event.
 */
class AddImage
{

    use \CultuurNet\UDB3\AddImageTrait;

    /**
     * @param string $id
     * @param Image $image
     */
    public function __construct($id, Image $image)
    {
        $this->id = $id;
        $this->image = $image;
    }
}
