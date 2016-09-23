<?php

namespace CultuurNet\UDB3\PriceInfo;

use ValueObjects\Enum\Enum;

/**
 * @method static $this BASE()
 * @method static $this TARIFF()
 */
class PriceCategory extends Enum
{
    const BASE = 'base';
    const TARIFF = 'tariff';
}
