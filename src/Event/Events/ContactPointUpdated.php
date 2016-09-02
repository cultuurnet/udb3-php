<?php

namespace CultuurNet\UDB3\Event\Events;

use CultuurNet\UDB3\Offer\Events\AbstractContactPointUpdated;

class ContactPointUpdated extends AbstractContactPointUpdated
{
    use BackwardsCompatibleEventTrait;
}
