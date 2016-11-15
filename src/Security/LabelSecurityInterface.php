<?php

namespace CultuurNet\UDB3\Security;

use ValueObjects\Identity\UUID;
use ValueObjects\String\String as StringLiteral;

interface LabelSecurityInterface
{
    /**
     * @return bool
     */
    public function isIdentifiedByUuid();

    /**
     * @return StringLiteral|null
     */
    public function getName();

    /**
     * @return UUID|null
     */
    public function getUuid();

    /**
     * @return bool
     */
    public function isAlwaysAllowed();
}
