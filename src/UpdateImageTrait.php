<?php

/**
 * @file
 * Contains CultuurNet\UDB3\UpdateImageTrait.
 */

namespace CultuurNet\UDB3;

/**
 * Provides a trait for updating image commands.
 */
trait UpdateImageTrait
{

    /**
     * Id that gets updated.
     * @var string
     */
    protected $id;

    /**
     * The index to be updated.
     * @var int
     */
    protected $indexToUpdate;

    /**
     * The mediaObject
     * @var MediaObject
     */
    protected $mediaObject;

    /**
     * @return string
     */
    function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    function getIndexToUpdate()
    {
        return $this->indexToUpdate;
    }

    /**
     * @return MediaObject
     */
    function getMediaObject()
    {
        return $this->mediaObject;
    }
}
