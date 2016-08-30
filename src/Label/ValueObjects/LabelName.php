<?php

namespace CultuurNet\UDB3\Label\ValueObjects;

use ValueObjects\String\String as StringLiteral;

/**
 * Class LabelName
 * @package CultuurNet\UDB3\Label\ValueObjects
 */
class LabelName extends StringLiteral
{
    /**
     * @param string $value
     * @param bool $visible
     */
    public function __construct($value)
    {
        if (false !== strpos($value, ';')) {
            throw new \InvalidArgumentException(
                'Value for argument $value should not contain semicolons.'
            );
        }

        $length = mb_strlen($value);
        if ($length < 3) {
            throw new \InvalidArgumentException(
                'Value for argument $value should not be shorter than 3 chars.'
            );
        }

        if ($length > 255) {
            throw new \InvalidArgumentException(
                'Value for argument $value should not be longer than 255 chars.'
            );
        }

        // checks if the value is a string, etc.
        parent::__construct($value);
    }
}
