<?php

/**
 * @file
 * Contains CultuurNet\UDB3\Place\Events\ImageAdded.
 */

namespace CultuurNet\UDB3\Place\Events;

use CultuurNet\UDB3\Media\MediaObject;
use CultuurNet\UDB3\Place\PlaceEvent;

/**
 * Provides an ImageAdded event.
 */
class ImageAdded extends PlaceEvent
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
        return new static($data['place_id'], MediaObject::deserialize($data['media_object']));
    }
}
