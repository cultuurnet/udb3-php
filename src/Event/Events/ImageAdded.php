<?php

namespace CultuurNet\UDB3\Event\Events;

use CultuurNet\UDB3\MediaObject;
use CultuurNet\UDB3\Offer\Events\AbstractEvent;

/**
 * Provides an ImageAdded event.
 */
class ImageAdded extends AbstractEvent
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
