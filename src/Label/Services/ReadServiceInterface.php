<?php

namespace CultuurNet\UDB3\Label\Services;

use CultuurNet\UDB3\Label\ReadModels\JSON\Repository\Entity;
use CultuurNet\UDB3\Label\ReadModels\JSON\Repository\Query;
use ValueObjects\Identity\UUID;
use ValueObjects\Number\Natural;

interface ReadServiceInterface
{
    /**
     * @param UUID $uuid
     * @return Entity|null
     */
    public function getByUuid(UUID $uuid);

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