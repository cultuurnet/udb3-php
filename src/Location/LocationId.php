<?php

namespace CultuurNet\UDB3\Location;

use ValueObjects\StringLiteral\StringLiteral;

class LocationId extends StringLiteral
{
    public function __construct($value)
    {
        parent::__construct($value);

        if (empty($value)) {
            throw new \InvalidArgumentException('LocationId can\'t have an empty value.');
        }
    }
}
