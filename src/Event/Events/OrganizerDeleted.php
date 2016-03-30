<?php

namespace CultuurNet\UDB3\Event\Events;

use CultuurNet\UDB3\Offer\Events\AbstractOrganizerDeleted;

class OrganizerDeleted extends AbstractOrganizerDeleted
{
    use BackwardsCompatibleEventTrait;
}
