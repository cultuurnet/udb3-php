<?php

namespace CultuurNet\UDB3\SavedSearches\Command;

use ValueObjects\StringLiteral\StringLiteral;

class UnsubscribeFromSavedSearch extends SavedSearchCommand
{
    /**
     * @var String
     */
    protected $searchId;

    /**
     * {@inheritdoc}
     * @param StringLiteral $searchId
     */
    public function __construct(StringLiteral $userId, StringLiteral $searchId)
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
