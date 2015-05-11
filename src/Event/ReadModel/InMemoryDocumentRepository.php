<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Event\ReadModel;

class InMemoryDocumentRepository implements DocumentRepositoryInterface
{
    private $documents;

    public function get($id)
    {
        if (isset($this->documents[$id])) {
            return $this->documents[$id];
        }
    }

    public function save(JsonDocument $readModel)
    {
        $this->documents[$readModel->getId()] = $readModel;
    }

    public function delete($id)
    {
        if (isset($this->documents[$id])) {
            unset($this->documents[$id]);
        }
        return $id;
    }
}
