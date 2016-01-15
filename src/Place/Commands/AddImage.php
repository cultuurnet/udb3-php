<?php

/**
 * @file
 * Contains CultuurNet\UDB3\Place\Commands\AddImage.
 */

namespace CultuurNet\UDB3\Place\Commands;

use CultuurNet\UDB3\Media\Image;

/**
 * Provides a command to add an image to the place.
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
