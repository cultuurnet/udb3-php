<?php

namespace CultuurNet\UDB3\Event\Events;

use CultuurNet\UDB3\Media\MediaObject;
use CultuurNet\UDB3\Offer\Events\AbstractEvent;
use CultuurNet\UDB3\Offer\ImageUpdateTrait;
use ValueObjects\String\String;

/**
 * Provides an ImageUpdated event.
 */
class ImageUpdated extends AbstractEvent
{
    use ImageUpdateTrait;
    use BackwardsCompatibleEventTrait;
}
