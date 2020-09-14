<?php

namespace CultuurNet\UDB3\Event\Events;

use CultuurNet\UDB3\Offer\Events\AbstractOrganizerUpdated;

class OrganizerUpdated extends AbstractOrganizerUpdated
{
    use BackwardsCompatibleEventTrait;
}
