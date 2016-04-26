<?php

namespace CultuurNet\UDB3\Event\Events;

use CultuurNet\UDB3\Offer\Events\AbstractDescriptionUpdated;

class DescriptionUpdated extends AbstractDescriptionUpdated
{
    use BackwardsCompatibleEventTrait;
}
