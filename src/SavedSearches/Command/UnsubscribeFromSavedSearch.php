<?php

namespace CultuurNet\UDB3\SavedSearches\Command;

use ValueObjects\String\String;

class UnsubscribeFromSavedSearch extends SavedSearchCommand
{
    /**
     * @var String
     */
    protected $searchId;

    /**
     * {@inheritdoc}
     * @param String $searchId
     */
    public function __construct(String $userId, String $searchId)
    {
        parent::__construct($userId);
        $this->searchId = $searchId;
    }

    /**
     * @return String
     */
    public function getSearchId()
    {
        return $this->searchId;
    }
}
