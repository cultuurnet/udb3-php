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
     */
    public function __construct($id, $indexToDelete)
    {
        parent::__construct($id);
        $this->indexToDelete = $indexToDelete;
    }

    /**
     * @return mixed The object instance
     */
    public static function deserialize(array $data)
    {
        return new static($data['place_id'], MediaObject::deserialize($data['index_to_delete']));
    }
}
