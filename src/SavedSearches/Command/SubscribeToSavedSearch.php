<?php

namespace CultuurNet\UDB3\SavedSearches\Command;

use \CultureFeed_SavedSearches_SavedSearch as SavedSearch;
use CultuurNet\UDB3\SavedSearches\Properties\QueryString;
use ValueObjects\String\String;

class SubscribeToSavedSearch
{
    /**
     * @var String
     */
    protected $userId;

    /**
     * @var String
     */
    protected $name;

    /**
     * @var QueryString
     */
    protected $query;

    /**
     * @param String $userId
     * @param String $name
     * @param QueryString $query
     */
    public function __construct(String $userId, String $name, QueryString $query)
    {
        $this->userId = $userId;
        $this->name = $name;
        $this->query = $query;
    }

    /**
     * @return String
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * @return String
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return QueryString
     */
    public function getQuery()
    {
        return $this->query;
    }
}
