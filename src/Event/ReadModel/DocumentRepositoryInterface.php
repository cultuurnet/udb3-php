<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Event\ReadModel;

use CultuurNet\UDB3\ReadModel\JsonDocument;

interface DocumentRepositoryInterface
{
    public function get($id);

    public function save(JsonDocument $readModel);

    public function delete($id);
}
