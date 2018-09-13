<?php

namespace CultuurNet\UDB3\MyOrganizers\ReadModel;

use DateTimeInterface;

interface RepositoryInterface
{
    /**
     * @param string $id
     * @param string $userId
     * @param DateTimeInterface $created
     * @return void
     */
    public function add(
        string $id,
        string $userId,
        DateTimeInterface $created
    );

    /**
     * @param string $id
     * @return void
     */
    public function delete(string $id);

    /**
     * @param string $id
     * @param DateTimeInterface $updated
     * @return void
     */
    public function setUpdateDate(string $id, DateTimeInterface $updated);
}
