<?php

namespace CultuurNet\UDB3\ReadModel\Index;

use ValueObjects\Enum\Enum;

/**
 * @method static EntityType EVENT()
 * @method static EntityType ORGANIZER()
 * @method static EntityType PLACE()
 */
class EntityType extends Enum
{
    const EVENT = 'event';
    const ORGANIZER = 'organizer';
    const PLACE = 'place';
}
