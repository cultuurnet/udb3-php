<?php

namespace CultuurNet\UDB3\SavedSearches\Command;

use CultuurNet\UDB3\SavedSearches\Properties\QueryString;
use ValueObjects\String\String;

class SubscribeToSavedSearch extends SavedSearchCommand
{
    /**
     * @var String
     */
    protected $name;

    /**
     * @var QueryString
     */
    protected $query;

    /**
     * {@inheritdoc}
     * @param String $name
     * @param QueryString $query
     */
    public function __construct(String $userId, String $name, QueryString $query)
    {
        parent::__construct($userId);
        $this->name = $name;
        $this->query = $query;
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
