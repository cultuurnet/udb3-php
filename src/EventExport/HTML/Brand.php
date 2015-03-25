<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\EventExport\HTML;

use ValueObjects\String\String;

class Brand extends String
{
    public function __construct($brand)
    {
        parent::__construct($brand);

        $knownBrands = ['uit', 'uitpas', 'vlieg'];

        if (!in_array($brand, $knownBrands)) {
            throw new InvalidBrandException(
                "Unknown brand '{$brand}'', use one of: " .
                implode(', ', $knownBrands)
            );
        }
    }
}
