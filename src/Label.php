<?php
/**
 * @file
 */

namespace CultuurNet\UDB3;

use CultuurNet\Entry\Keyword;

class Label extends Keyword
{
    public function __construct($value, $visible = true)
    {
        if (!is_string($value)) {
            throw new \InvalidArgumentException(
                'Value for argument $value should be a string, got a value of type ' . gettype($value)
            );
        }

        if (!is_bool($visible)) {
            throw new \InvalidArgumentException(
                'Value for argument $visible should be a boolean, got a value of type ' . gettype($visible)
            );
        }

        parent::__construct($value, $visible);
    }

    /**
     * @param Label $label
     * @return bool
     */
    public function equals(Label $label)
    {
        return strcmp(
            mb_strtolower((string) $this, 'UTF-8'),
            mb_strtolower((string) $label, 'UTF-8')
        ) == 0;
    }
}
