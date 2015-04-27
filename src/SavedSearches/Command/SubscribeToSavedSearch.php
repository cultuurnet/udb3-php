<?php

namespace CultuurNet\UDB3\SavedSearches\Command;

use \CultureFeed_SavedSearches_SavedSearch as SavedSearch;

class SubscribeToSavedSearch
{
    /**
     * @var string
     */
    protected $userId;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $query;

    /**
     * @param string $userId
     * @param string $name
     * @param string $query
     *
     * @throws \InvalidArgumentException
     *   When an invalid frequency value is given.
     */
    public function __construct($userId, $name, $query)
    {
        $this->userId = $userId;
        $this->name = $name;
        $this->query = $query;
    }

    /**
     * @return string
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getQuery()
    {
        return $this->query;
    }
}
