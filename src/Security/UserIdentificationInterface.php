<?php

namespace CultuurNet\UDB3\Security;

use ValueObjects\String\String as StringLiteral;

interface UserIdentificationInterface
{
    /**
     * @return bool
     */
    public function isGodUser();

    /**
     * @return StringLiteral|null
     */
    public function getId();
}
