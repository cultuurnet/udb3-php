<?php

namespace CultuurNet\UDB3\PriceInfo;

use ValueObjects\Exception\InvalidNativeArgumentException;
use ValueObjects\Number\Real;

class Price extends Real
{
    public function __construct($value)
    {
        parent::__construct($value);

        if ($value < 0) {
            throw new InvalidNativeArgumentException($value, ['float (>=0)']);
        }
    }
}
