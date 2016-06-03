<?php

namespace CultuurNet\UDB3\Label\Services;

use CultuurNet\UDB3\Label\ValueObjects\Privacy;
use CultuurNet\UDB3\Label\ValueObjects\Visibility;
use ValueObjects\String\String as StringLiteral;

interface WriteServiceInterface
{
    /**
     * @param StringLiteral $name
     * @param Visibility $visibility
     * @param Privacy $privacy
     * @return WriteResult
     */
    public function create(
        StringLiteral $name,
        Visibility $visibility,
        Privacy $privacy
    );
}
