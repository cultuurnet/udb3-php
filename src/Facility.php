<?php

/**
 * @file
 * Contains CultuurNet\UDB3\Facility.
 */

namespace CultuurNet\UDB3;

/**
 * Instantiates a Facility category.
 */
class Facility extends Category
{

    const DOMAIN = 'facility';

    public function __construct($id, $label)
    {
        parent::__construct($id, $label, self::DOMAIN);
    }
}
