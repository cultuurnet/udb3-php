<?php

namespace CultuurNet\UDB3\Event\Commands;

use CultuurNet\UDB3\Offer\ImageUpdateTrait;

/**
 * Provides a command to update an image of the event.
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
