<?php

namespace CultuurNet\UDB3\Event\Events;

use Broadway\Serializer\SerializableInterface;
use CultuurNet\UDB3\Media\MediaObject;
use CultuurNet\UDB3\Offer\Events\AbstractEvent;
use CultuurNet\UDB3\Offer\ImageUpdateTrait;
use ValueObjects\String\String;

/**
 * Provides an ImageUpdated event.
 */
class ImageUpdated extends AbstractEvent implements SerializableInterface
{
    use ImageUpdateTrait;
    use BackwardsCompatibleEventTrait;
}
