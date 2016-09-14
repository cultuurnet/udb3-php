<?php

namespace CultuurNet\UDB3\Security;

use ValueObjects\String\String as StringLiteral;

class AnonymousUserIdentification implements UserIdentificationInterface
{
    /**
     * @return bool
     */
    public function isGodUser()
    {
        return false;
    }

    /**
     * @return StringLiteral|null
     */
    public function getId()
    {
        return null;
    }
}
