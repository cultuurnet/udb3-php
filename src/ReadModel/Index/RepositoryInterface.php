<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\ReadModel\Index;

use \DateTimeInterface;

interface RepositoryInterface
{
    /**
     * @param string $id
     * @param EntityType $entityType
     * @param string $userId
     * @param string $name
     * @param string $postalCode
     * @param DateTimeInterface $created
     * @return void
     */
    public function updateIndex(
        $id,
        EntityType $entityType,
        $userId,
        $name,
        $postalCode,
        DateTimeInterface $created = null
    );

    /**
     * @param string$id
     * @return void
     */
    public function deleteIndex($id, EntityType $entityType);

    /**
     * @param string $id
     * @param DateTimeInterface $updated
     * @return void
     */
    public function setUpdateDate($id, DateTimeInterface $updated);
}
