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
     * The internal file id.
     * @var mixed int|string
     */
    protected $internalId;

    /**
     * @return int
     */
    public function getIndexToDelete()
    {
        return $this->indexToDelete;
    }

    /**
     * @return mixed int|string
     */
    public function getInternalId()
    {
        return $this->internalId;
    }

    /**
     * {@inheritdoc}
     */
    public function serialize()
    {
        return parent::serialize() + array(
            'index_to_delete' => $this->indexToDelete,
            'internal_id' => $this->internalId
        );
    }
}
