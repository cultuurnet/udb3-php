<?php

namespace CultuurNet\UDB3\Place\Commands;

use CultuurNet\UDB3\Offer\ImageUpdateTrait;

/**
 * Provides a command to update an image of the place.
 */
class UpdateImage
{
    use ImageUpdateTrait;

    /**
     * The id of the item that has its image updated.
     * @var int
     */
    protected $itemId;
}
