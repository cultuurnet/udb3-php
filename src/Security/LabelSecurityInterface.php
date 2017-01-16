<?php

namespace CultuurNet\UDB3\Security;

use ValueObjects\StringLiteral\StringLiteral;

interface LabelSecurityInterface
{
    /**
     * @return StringLiteral|null
     */
    public function getName();
}
