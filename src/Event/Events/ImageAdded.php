<?php

namespace CultuurNet\UDB3\Event\Events;

use CultuurNet\UDB3\Media\Image;
use CultuurNet\UDB3\Offer\Events\AbstractEvent;

/**
 * Provides an ImageAdded event.
 */
class ImageAdded extends AbstractEvent
{
    use \CultuurNet\UDB3\ImageAddedTrait;
    use BackwardsCompatibleEventTrait;

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
