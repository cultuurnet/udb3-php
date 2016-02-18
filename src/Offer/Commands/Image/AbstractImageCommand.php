<?php

namespace CultuurNet\UDB3\Offer\Commands\Image;

use CultuurNet\UDB3\Media\Image;

abstract class AbstractImageCommand
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
     * @param $itemId
     *  The id of the item that is targeted by the command.
     *
     * @param Image $image
     *  The image that is used in the command.
     */
    public function __construct($itemId, Image $image)
    {
        $this->image = $image;
        $this->itemId = $itemId;
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
}
