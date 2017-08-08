<?php

namespace CultuurNet\UDB3\Place\Events;

use CultuurNet\UDB3\Offer\Events\AbstractTitleUpdated;

class TitleUpdated extends AbstractTitleUpdated
{
    use BackwardsCompatibleEventTrait;
}
