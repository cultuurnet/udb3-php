<?php

namespace CultuurNet\UDB3\Event\Events;

use CultuurNet\UDB3\Offer\Events\AbstractEvent;

/**
 * Event when typical age range was deleted
 */
class TypicalAgeRangeDeleted extends AbstractEvent
{
    use BackwardsCompatibleEventTrait;
}
