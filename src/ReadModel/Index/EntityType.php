<?php

namespace CultuurNet\UDB3\ReadModel\Index;

use ValueObjects\Enum\Enum;

class EntityType extends Enum
{
    const EVENT = 'event';
    const ORGANIZER = 'organizer';
    const PLACE = 'place';
}
