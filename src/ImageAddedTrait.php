<?php

/**
 * @file
 * Contains CultuurNet\UDB3\ImageAddedTrait.
 */

namespace CultuurNet\UDB3;

/**
 * Trait for the ImageAdded events.
 */
trait ImageAddedTrait
{

    /**
     * The added media object.
     * @var MediaObject
     */
    protected $mediaObject;

    /**
     * @return MediaObject
     */
    public function getMediaObject()
    {
        return $this->mediaObject;
    }

    /**
     * {@inheritdoc}
     */
    public function serialize()
    {
        return parent::serialize() + array(
            'media_object' => $this->mediaObject->serialize(),
        );
    }
}
