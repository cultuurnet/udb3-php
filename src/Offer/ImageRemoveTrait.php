<?php

namespace CultuurNet\UDB3\Offer;

use CultuurNet\UDB3\Media\Image;

/**
 * Provides a trait for deleting an image commands.
 */
trait ImageRemoveTrait
{
    /**
     * @var string
     */
    protected $itemId;

    /**
     * @var Image
     */
    protected $image;

    /**
     * @param string $id
     * @param Image $image
     */
    public function __construct($id, Image $image)
    {
        $this->itemId = $id;
        $this->image = $image;
    }

    /**
     * @return string
     */
    public function getItemId()
    {
        return $this->itemId;
    }

    /**
     * @return Image
     */
    public function getImage()
    {
        return $this->image;
    }

    public function serialize()
    {
        return array(
            'item_id' => $this->itemId,
            'image' => $this->image->serialize()
        );
    }

    public static function deserialize(array $data)
    {
        return new static(
            $data['item_id'],
            Image::deserialize($data['image'])
        );
    }
}
