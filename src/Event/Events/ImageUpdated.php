<?php

namespace CultuurNet\UDB3\Event\Events;

use CultuurNet\UDB3\Event\EventEvent;
use CultuurNet\UDB3\Media\MediaObject;
use CultuurNet\UDB3\Offer\ImageUpdateTrait;
use ValueObjects\String\String;

/**
 * Provides an ImageUpdated event.
 */
class ImageUpdated extends EventEvent
{
    use ImageUpdateTrait;
}
