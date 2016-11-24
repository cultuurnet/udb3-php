<?php

namespace CultuurNet\UDB3\Security;

use ValueObjects\String\String as StringLiteral;

interface LabelSecurityInterface
{
    /**
     * @return StringLiteral|null
     */
    public function getName();
}
