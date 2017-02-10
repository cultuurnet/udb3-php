<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\ReadModel\Index;

use \DateTimeInterface;
use ValueObjects\Web\Domain;

interface RepositoryInterface
{
    /**
     * @param string $id
     * @param EntityType $entityType
     * @param string $userId
     * @param Domain $owningDomain
     * @param DateTimeInterface $created
     * @return void
     */
    public function updateIndex(
        $id,
        EntityType $entityType,
        $userId,
        Domain $owningDomain,
        DateTimeInterface $created = null
    );

    /**
     * @param string $id
     * @param EntityType $entityType
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
