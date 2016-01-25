<?php

/**
 * @file
 * Contains CultuurNet\UDB3\ImageUpdatedTrait.
 */

namespace CultuurNet\UDB3;

use CultuurNet\UDB3\Media\MediaObject;

/**
 * Trait for the ImageUpdated events.
 */
trait ImageUpdatedTrait
{

    /**
     * The index to update.
     * @var int
     */
    protected $indexToUpdate;

    /**
     * The updated media object.
     * @var MediaObject
     */
    protected $mediaObject;

    /**
     * @return int
     */
    public function getIndexToUpdate()
    {
        return $this->indexToUpdate;
    }

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
            'index_to_update' => $this->indexToUpdate,
            'media_object' => $this->mediaObject->serialize(),
        );
    }
}
