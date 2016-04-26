<?php

namespace CultuurNet\UDB3\Place\Events;

use CultuurNet\UDB3\Offer\Events\AbstractContactPointUpdated;

class ContactPointUpdated extends AbstractContactPointUpdated
{
    use BackwardsCompatibleEventTrait;
}
