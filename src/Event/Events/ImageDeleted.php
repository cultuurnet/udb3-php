<?php

/**
 * @file
 * Contains CultuurNet\UDB3\Event\Events\ImageDeleted.
 */

namespace CultuurNet\UDB3\Event\Events;

use CultuurNet\UDB3\Event\EventEvent;

/**
 * Provides an ImageDeleted event.
 */
class ImageDeleted extends EventEvent
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
        return new static($data['event_id'], MediaObject::deserialize($data['index_to_delete']));
    }
}
