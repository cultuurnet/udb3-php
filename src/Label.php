<?php
/**
 * @file
 */

namespace CultuurNet\UDB3;

use CultuurNet\Entry\Keyword;

class Label extends Keyword
{
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
