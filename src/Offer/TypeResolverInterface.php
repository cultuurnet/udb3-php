<?php

namespace CultuurNet\UDB3\Offer;

use CultuurNet\UDB3\Category;
use ValueObjects\StringLiteral\StringLiteral;

interface TypeResolverInterface
{
    /**
     * @param StringLiteral $typeId
     * @return Category
     */
    public function byId(StringLiteral $typeId);
}
