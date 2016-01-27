<?php

namespace CultuurNet\UDB3\Place\Events;

use CultuurNet\UDB3\Offer\ImageUpdateTrait;
use CultuurNet\UDB3\Place\PlaceEvent;

/**
 * Provides an ImageUpdated event.
 */
class ImageUpdated extends PlaceEvent
{
    use ImageUpdateTrait;
}
