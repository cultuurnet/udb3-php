<?php

namespace CultuurNet\UDB3\Media\Properties;

use ValueObjects\Exception\InvalidNativeArgumentException;
use ValueObjects\String\String as StringLiteral;

class CopyrightHolder extends StringLiteral
{
    public function __construct($value)
    {
        if (false === \is_string($value)) {
            throw new InvalidNativeArgumentException($value, array('string'));
        }

        $paddedValue = str_pad($value, 2, '.');

        return parent::__construct(substr($paddedValue, 0, 250));
    }
}
