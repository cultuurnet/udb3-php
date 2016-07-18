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
     * @param string $name
     * @param int $limit
     * @param int $start
     */
    public function search($name, $limit, $start);

    /**
     * @param string $uuid
     * @param string $name
     */
    public function update($uuid, $name);

}
