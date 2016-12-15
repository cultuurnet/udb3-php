<?php

namespace CultuurNet\UDB3\Offer\Events\Image;

use CultuurNet\UDB3\Media\Image;
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

    /**
     * {@inheritdoc}
     */
    public function serialize()
    {
        return parent::serialize() + array(
            'main_image' => $this->images->getMain()->serialize(),
            'images' => array_map(
                function (Image $image) {
                    return $image->serialize();
                },
                $this->images->toArray()
            )
        );
    }

    /**
     * @param array $data
     * @return static
     */
    public static function deserialize(array $data)
    {
        $images = ImageCollection::fromArray(
            array_map(
                function ($imageData) {
                    return Image::deserialize($imageData);
                },
                $data['images']
            )
        );

        return new static(
            $data['item_id'],
            isset($data['main_image'])
                ? $images->withMain(Image::deserialize($data['main_image']))
                : $images
        );
    }
}
