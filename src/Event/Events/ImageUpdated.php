<?php

/**
 * @file
 * Contains CultuurNet\UDB3\Event\Events\ImageUpdated.
 */

namespace CultuurNet\UDB3\Event\Events;

use CultuurNet\UDB3\Event\EventEvent;
use CultuurNet\UDB3\MediaObject;

/**
 * Provides an ImageUpdated event.
 */
class ImageUpdated extends EventEvent
{
    use \CultuurNet\UDB3\ImageUpdatedTrait;

    /**
     * @param string $id
     * @param int $indexToUpdate
     * @param MediaObject $mediaObject
     */
    public function __construct($id, $indexToUpdate, $mediaObject)
    {
        parent::__construct($id);
        $this->indexToUpdate = $indexToUpdate;
        $this->mediaObject = $mediaObject;
    }

    /**
     * @return mixed The object instance
     */
    public static function deserialize(array $data)
    {
        return new static($data['place_id'], $data['index_to_update'], MediaObject::deserialize($data['media_object']));
    }
}
