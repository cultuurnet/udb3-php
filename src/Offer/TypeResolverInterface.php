<?php

namespace CultuurNet\UDB3\Offer;

use CultuurNet\UDB3\Event\EventType;
use ValueObjects\StringLiteral\StringLiteral;

interface TypeResolverInterface
{
    /**
     * @param StringLiteral $typeId
     * @return EventType
     */
    public function byId(StringLiteral $typeId);
}
