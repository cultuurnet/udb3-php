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
            throw new \InvalidArgumentException(sprintf(
                'Value for argument $value should be a string, got a value of type %s.',
                gettype($value)
            ));
        }

        if (!is_bool($visible)) {
            throw new \InvalidArgumentException(sprintf(
                'Value for argument $visible should be a boolean, got a value of type %s.',
                gettype($visible)
            ));
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
