<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\ReadModel\Index;

interface RepositoryInterface
{
    public function updateIndex($id, $type, $userId, $name, $zip);
    public function getOrganizersByTitle($title, $limit = 10);
    public function deleteIndex($id);
}
