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
     * @param Query $query
     * @return Results
     */
    public function search(Query $query);

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
