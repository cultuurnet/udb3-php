<?php

namespace CultuurNet\UDB3\Event\Events;

use CultuurNet\UDB3\Event\EventEvent;
use CultuurNet\UDB3\Offer\ImageRemoveTrait;

/**
 * Provides an ImageRemoved event.
 */
class ImageRemoved extends EventEvent
{
    use ImageRemoveTrait;
}
