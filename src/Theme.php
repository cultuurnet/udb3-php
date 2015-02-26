<?php

/**
 * @file
 * Contains CultuurNet\UDB3\Theme.
 */

namespace CultuurNet\UDB3;

/**
 * Instantiates a Theme category.
 */
class Theme extends Category
{

    const DOMAIN = 'theme';

    public function __construct($id, $label)
    {
        parent::__construct($id, $label, self::DOMAIN);
    }

}
