<?php

namespace CultuurNet\UDB3\Variations\ReadModel\JSONLD;

use CultuurNet\UDB3\ReadModel\JsonDocument;
use CultuurNet\UDB3\Variations\Model\Properties\Id;

interface RepositoryInterface
{
    /**
     * @param Id $id
     * @return JsonDocument
     */
    public function get(Id $id);

    public function save(JsonDocument $readModel);
}
