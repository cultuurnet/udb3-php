<?php

namespace CultuurNet\UDB3\Organizer\ReadModel\Search;

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
     * @param string|null $constraint
     */
    public function save($uuid, $name, $constraint = null);

    /**
     * @param string $query
     * @param int $limit
     * @param int $start
     * @return Results
     */
    public function search($query = '', $limit = 10, $start = 0);

    /**
     * @param string $uuid
     * @param $title
     * @return
     */
    public function updateTitle($uuid, $title);

    /**
     * @param string $uuid
     * @param string $website
     */
    public function updateWebsite($uuid, $website);
}
