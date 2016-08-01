<?php

namespace CultuurNet\UDB3\Role\ReadModel\Search;

interface RepositoryInterface
{
    /**
     * @param string $uuid
     * @return mixed
     */
    public function remove($uuid);

    /**
     * @param string $uuid
     * @param string $name
     */
    public function save($uuid, $name);

    /**
     * @param string $query
     * @param int $limit
     * @param int $start
     * @return Results
     */
    public function search($query = '', $limit = 10, $start = 0);

    /**
     * @param string $uuid
     * @param string $name
     */
    public function update($uuid, $name);
}
