<?php

/**
 * @file
 * Contains CultuurNet\UDB3\ImageAddedTrait.
 */

namespace CultuurNet\UDB3;

use CultuurNet\UDB3\Media\Image;

/**
 * Trait for the ImageAdded events.
 */
trait ImageAddedTrait
{
    /**
     * The added media object.
     * @var Image
     */
    protected $image;

    /**
     * @return Image
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * {@inheritdoc}
     */
    public function serialize()
    {
        return parent::serialize() + array(
            'image' => $this->image->serialize(),
        );
    }
}
