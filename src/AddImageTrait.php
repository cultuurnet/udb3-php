<?php

/**
 * @file
 * Contains CultuurNet\UDB3\AddImageTrait.
 */

namespace CultuurNet\UDB3;

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
     * @return MediaObject
     */
    function getMediaObject()
    {
        return $this->mediaObject;
    }
}
