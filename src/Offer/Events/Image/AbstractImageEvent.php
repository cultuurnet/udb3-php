<?php

namespace CultuurNet\UDB3\Offer\Events\Image;

use CultuurNet\UDB3\Media\Image;
use CultuurNet\UDB3\Offer\Events\AbstractEvent;

/**
 * Abstract because it should be implemented in the namespace of each concrete
 * offer implementation. (Place, Event, ...)
 */
abstract class AbstractImageEvent extends AbstractEvent
{
    /**
     * @var Image
     */
    protected $image;

    /**
     * {@inheritdoc}
     *
     * @param Image $image
     *  The image that is involved in the event.
     */
    public function __construct($itemId, Image $image)
    {
        parent::__construct($itemId);
        $this->image = $image;
    }

    /**
     * @return Image
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * @return array
     */
    public function serialize()
    {
        return parent::serialize() + array(
            'image' => $this->image->serialize(),
        );
    }

    /**
     * @param array $data
     * @return mixed The object instance
     */
    public static function deserialize(array $data)
    {
        return new static(
            $data['item_id'],
            Image::deserialize($data['image'])
        );
    }
}
