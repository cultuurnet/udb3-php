<?php

namespace CultuurNet\UDB3\Offer\Events\Image;

use CultuurNet\UDB3\Media\ImageCollection;
use CultuurNet\UDB3\Offer\Events\AbstractEvent;

abstract class AbstractImagesEvent extends AbstractEvent
{
    /**
     * @var ImageCollection
     */
    protected $images;

    /**
     * @param string $eventId
     * @param ImageCollection $images
     */
    public function __construct($eventId, ImageCollection $images)
    {
        parent::__construct($eventId);
        $this->images = $images;
    }

    /**
     * @return ImageCollection
     */
    public function getImages()
    {
        return $this->images;
    }
}
