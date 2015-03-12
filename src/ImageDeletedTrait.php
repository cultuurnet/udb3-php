<?php

/**
 * @file
 * Contains CultuurNet\UDB3\ImageDeletedTrait.
 */

namespace CultuurNet\UDB3;

/**
 * Trait for the ImageDeleted events.
 */
trait ImageDeletedTrait
{

    /**
     * The index to delete.
     * @var int
     */
    protected $indexToDelete;

    /**
     * @return int
     */
    public function getIndexToDelete()
    {
        return $this->indexToDelete;
    }

    /**
     * {@inheritdoc}
     */
    public function serialize()
    {
        return parent::serialize() + array(
            'index_to_delete' => $this->indexToDelete,
        );
    }
}
