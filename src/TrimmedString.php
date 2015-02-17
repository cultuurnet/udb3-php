<?php
/**
 * @file
 */

namespace CultuurNet\UDB3;

use ValueObjects\String\String;

abstract class TrimmedString extends String
{
    public function __construct($value)
    {
        if (is_string($value)) {
            $value = trim($value);
        }

        parent::__construct($value);
    }
}
