<?php
/**
 * @file
 */

namespace CultuurNet\UDB3;

use CultuurNet\Entry\Keyword;
use CultuurNet\UDB3\Label\ValueObjects\LabelName;

class Label extends Keyword
{
    public function __construct($value, $visible = true)
    {
        // Try constructing a LabelName object, so the same validation rules hold.
        $labelName = new LabelName($value);

        if (!is_bool($visible)) {
            throw new \InvalidArgumentException(sprintf(
                'Value for argument $visible should be a boolean, got a value of type %s.',
                gettype($visible)
            ));
        }

        parent::__construct($labelName->toNative(), $visible);
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
