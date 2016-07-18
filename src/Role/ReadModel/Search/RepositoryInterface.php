<?php

namespace CultuurNet\UDB3\Role\ReadModel\Search;

use ValueObjects\Identity\UUID;
use ValueObjects\String\String as stringLiteral;

interface RepositoryInterface
{
    /**
     * @param UUID $uuid
     * @param stringLiteral $name
     * @return mixed
     */
    public function save(
        UUID $uuid,
        StringLiteral $name
    );

    /**
     * @param UUID $uuid
     * @return mixed
     */
    public function remove(UUID $uuid);
}
