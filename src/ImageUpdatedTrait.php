<?php

/**
 * @file
 * Contains CultuurNet\UDB3\ImageUpdatedTrait.
 */

namespace CultuurNet\UDB3;

/**
 * Trait for the ImageUpdated events.
 */
trait ImageUpdatedTrait
{

    /**
     * The index to update.
     * @var int
     */
    protected $indexToUpdated;

    /**
     * The updated media object.
     * @var MediaObject
     */
    protected $mediaObject;

    /**
     * @return int
     */
    public function getIndexToUpdated()
    {
        return $this->indexToUpdated;
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
            'index_to_update' => $this->indexToUpdated,
            'media_object' => $this->mediaObject->serialize(),
        );
    }
}
