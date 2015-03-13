<?php

/**
 * @file
 * Contains CultuurNet\UDB3\Place\Events\ImageDeleted.
 */

namespace CultuurNet\UDB3\Place\Events;

use CultuurNet\UDB3\Place\PlaceEvent;

/**
 * Provides an ImageDeleted event.
 */
class ImageDeleted extends PlaceEvent
{
    use \CultuurNet\UDB3\ImageDeletedTrait;

    /**
     * @param string $id
     * @param int $indexToDelete
     * @param mixed int|string $internalId
     */
    public function __construct($id, $indexToDelete, $internalId)
    {
        parent::__construct($id);
        $this->indexToDelete = $indexToDelete;
        $this->internalId = $internalId;
    }

    /**
     * @return mixed The object instance
     */
    public static function deserialize(array $data)
    {
        return new static($data['place_id'], $data['index_to_delete'], $data['internal_id']);
    }
}
