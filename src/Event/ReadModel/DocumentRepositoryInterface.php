<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Event\ReadModel;

use CultuurNet\UDB3\ReadModel\JsonDocument;

interface DocumentRepositoryInterface
{
    /**
     * @param string $id
     * @return JsonDocument
     *
     * @throws DocumentGoneException
     */
    public function get($id);

    public function save(JsonDocument $readModel);

    public function remove($id);
}
