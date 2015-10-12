<?php

/**
 * @file
 * Contains CultuurNet\UDB3\Event\Events\ImageDeleted.
 */

namespace CultuurNet\UDB3\Event\Events;

use CultuurNet\UDB3\Event\EventEvent;
use CultuurNet\UDB3\MediaObject;

/**
 * Provides an ImageDeleted event.
 */
class ImageDeleted extends EventEvent
{
    use \CultuurNet\UDB3\ImageDeletedTrait;

    /**
     * @param string $id
     * @param int $indexToDelete
     * @parma mixed int|string $internalId
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
        return new static($data['event_id'], $data['index_to_delete'], $data['internal_id']);
    }
}
