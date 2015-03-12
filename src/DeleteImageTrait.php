<?php

/**
 * @file
 * Contains CultuurNet\UDB3\DeleteImageTrait.
 */

namespace CultuurNet\UDB3;

/**
 * Provides a trait for deleting an image commands.
 */
trait DeleteImageTrait
{

    /**
     * Id that gets updated.
     * @var string
     */
    protected $id;

    /**
     * The index to delete
     * @var int
     */
    protected $indexToDelete;

    /**
     * @return string
     */
    function getId()
    {
        return $this->id;
    }

    /**
     * @return ing
     */
    function getIndexToDelete()
    {
        return $this->indexToDelete;
    }
}
