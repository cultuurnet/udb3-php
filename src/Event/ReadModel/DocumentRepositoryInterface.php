<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Event\ReadModel;

interface DocumentRepositoryInterface
{
    /**
     * @param string $id
     * @return JsonDocument
     */
    public function get($id);

    public function save(JsonDocument $readModel);
}
