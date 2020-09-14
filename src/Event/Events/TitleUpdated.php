<?php

namespace CultuurNet\UDB3\Event\Events;

use CultuurNet\UDB3\Offer\Events\AbstractTitleUpdated;

class TitleUpdated extends AbstractTitleUpdated
{
    use BackwardsCompatibleEventTrait;
}
