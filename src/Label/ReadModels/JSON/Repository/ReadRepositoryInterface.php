<?php

namespace CultuurNet\UDB3\Label\ReadModels\JSON\Repository;

use ValueObjects\Identity\UUID;
use ValueObjects\String\String as StringLiteral;

interface ReadRepositoryInterface
{
    /**
     * @param UUID $uuid
     * @return Entity|null
     */
    public function getByUuid(UUID $uuid);

    /**
     * @param StringLiteral $name
     * @return Entity|null
     */
    public function getByName(StringLiteral $name);
}
