<?php

namespace CultuurNet\UDB3\SavedSearches\Command;

use ValueObjects\String\String;

abstract class SavedSearchCommand
{
    /**
     * @var String
     */
    protected $userId;

    /**
     * @param String $userId
     */
    public function __construct(String $userId)
    {
        $this->userId = $userId;
    }

    /**
     * @return String
     */
    public function getUserId()
    {
        return $this->userId;
    }
}
