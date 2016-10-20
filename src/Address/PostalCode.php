<?php

namespace CultuurNet\UDB3\Address;

use ValueObjects\String\String as StringLiteral;

/**
 * Postal Code
 */
class PostalCode extends StringLiteral
{
    /**
     * Returns a Postal Code object given a PHP native string or integer as parameter.
     *
     * @param string|integer $value
     */
    public function __construct($value)
    {
        $value = is_int($value) ? strval($value) : $value;
        parent::__construct($value);
    }
}
