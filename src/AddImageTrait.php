<?php

/**
 * @file
 * Contains CultuurNet\UDB3\AddImageTrait.
 */

namespace CultuurNet\UDB3;

use CultuurNet\UDB3\Media\Image;

/**
 * Provides a trait for adding image commands.
 */
trait AddImageTrait
{

    /**
     * Id that gets updated.
     * @var string
     */
    protected $id;

    /**
     * The image
     * @var Image
     */
    protected $image;

    /**
     * @return string
     */
    function getId()
    {
        return $this->id;
    }

    /**
     * @return Image
     */
    function getImage()
    {
        return $this->image;
    }
}
