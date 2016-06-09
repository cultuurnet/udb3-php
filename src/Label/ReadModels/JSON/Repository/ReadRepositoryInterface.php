<?php

namespace CultuurNet\UDB3\Label\ReadModels\JSON\Repository;

use ValueObjects\Identity\UUID;
use ValueObjects\Number\Natural;
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

    /**
     * @param Query $query
     * @return Entity[]|null
     */
    public function search(Query $query);

    /**
     * @param Query $query
     * @return Natural
     */
    public function searchTotalLabels(Query $query);
}
