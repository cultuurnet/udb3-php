<?php

namespace CultuurNet\UDB3\ReadModel\Index;

use ValueObjects\Enum\Enum;

/**
 * @method EntityType EVENT
 * @method EntityType ORGANIZER
 * @method EntityType PLACE
 */
class EntityType extends Enum
{
    const EVENT = 'event';
    const ORGANIZER = 'organizer';
    const PLACE = 'place';
}
