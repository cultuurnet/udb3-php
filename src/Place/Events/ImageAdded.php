<?php

/**
 * @file
 * Contains CultuurNet\UDB3\Place\Events\ImageAdded.
 */

namespace CultuurNet\UDB3\Place\Events;

use CultuurNet\UDB3\Media\Image;
use CultuurNet\UDB3\Place\PlaceEvent;

/**
 * Provides an ImageAdded event.
 */
class ImageAdded extends PlaceEvent
{
    use \CultuurNet\UDB3\ImageAddedTrait;

    /**
     * @param string $placeId
     * @param Image $image
     */
    public function __construct($placeId, Image $image)
    {
        parent::__construct($placeId);
        $this->image = $image;
    }

    /**
     * @return mixed The object instance
     */
    public static function deserialize(array $data)
    {
        return new static($data['place_id'], Image::deserialize($data['image']));
    }
}
