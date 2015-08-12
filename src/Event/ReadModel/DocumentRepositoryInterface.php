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
     *
     * @throws DocumentGoneException
     */
    public function get($id);

    public function save(JsonDocument $readModel);

    public function remove($id);
}
