<?php

/**
 * @file
 * Contains CultuurNet\UDB3\Event\Events\ImageAdded.
 */

namespace CultuurNet\UDB3\Event\Events;

use CultuurNet\UDB3\Event\EventEvent;
use CultuurNet\UDB3\Media\MediaObject;

/**
 * Provides an ImageAdded event.
 */
class ImageAdded extends EventEvent
{
    use \CultuurNet\UDB3\ImageAddedTrait;

    /**
     * @param string $id
     * @param MediaObject $mediaObject
     */
    public function __construct($id, $mediaObject)
    {
        parent::__construct($id);
        $this->mediaObject = $mediaObject;
    }

    /**
     * @return mixed The object instance
     */
    public static function deserialize(array $data)
    {
        return new static($data['event_id'], MediaObject::deserialize($data['media_object']));
    }
}
