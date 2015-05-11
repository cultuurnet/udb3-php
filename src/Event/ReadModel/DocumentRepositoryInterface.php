<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Event\ReadModel;

interface DocumentRepositoryInterface
{
    public function get($id);

    public function save(JsonDocument $readModel);

    public function delete($id);
}
