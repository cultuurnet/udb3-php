<?php

namespace CultuurNet\UDB3\Label\ValueObjects;

use ValueObjects\Enum\Enum;

/**
 * Class RelationType
 * @package CultuurNet\UDB3\Label\ValueObjects
 * @method static RelationType EVENT()
 * @method static RelationType PLACE()
 */
class RelationType extends Enum
{
    const EVENT = 'event';
    const PLACE = 'place';
}
