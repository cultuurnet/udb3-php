<?php

namespace CultuurNet\UDB3\Place\Events;

use CultuurNet\UDB3\Offer\Events\AbstractDescriptionUpdated;

class DescriptionUpdated extends AbstractDescriptionUpdated
{
    use BackwardsCompatibleEventTrait;
}
