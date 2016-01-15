<?php

/**
 * @file
 * Contains CultuurNet\UDB3\Event\Events\ImageAdded.
 */

namespace CultuurNet\UDB3\Event\Events;

use CultuurNet\UDB3\Event\EventEvent;
use CultuurNet\UDB3\Media\Image;

/**
 * Provides an ImageAdded event.
 */
class ImageAdded extends EventEvent
{
    use \CultuurNet\UDB3\ImageAddedTrait;

    /**
     * @param string $id
     * @param Image $image
     */
    public function __construct($id, Image $image)
    {
        parent::__construct($id);
        $this->image = $image;
    }

    /**
     * @return mixed The object instance
     */
    public static function deserialize(array $data)
    {
        return new static($data['event_id'], Image::deserialize($data['image']));
    }
}
