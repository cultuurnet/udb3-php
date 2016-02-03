<?php

/**
 * @file
 * Contains CultuurNet\UDB3\Place\Events\ImageDeleted.
 */

namespace CultuurNet\UDB3\Place\Events;

use CultuurNet\UDB3\Offer\ImageRemoveTrait;
use CultuurNet\UDB3\Place\PlaceEvent;

/**
 * Provides an ImageRemoved event.
 */
class ImageRemoved extends PlaceEvent
{
    use ImageRemoveTrait;
}
