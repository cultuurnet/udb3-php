<?php

/**
 * @file
 * Contains CultuurNet\UDB3\Theme.
 */

namespace CultuurNet\UDB3;

use CultuurNet\UDB3\Model\ValueObject\Taxonomy\Category\Category as Udb3ModelCategory;
use InvalidArgumentException;

final class Theme extends Category
{
    const DOMAIN = 'theme';

    public function __construct($id, $label)
    {
        parent::__construct($id, $label, self::DOMAIN);
    }
}
