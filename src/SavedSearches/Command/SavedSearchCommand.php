<?php

namespace CultuurNet\UDB3\SavedSearches\Command;

use ValueObjects\StringLiteral\StringLiteral;

abstract class SavedSearchCommand
{
    /**
     * @var String
     */
    protected $userId;

    /**
     * @param StringLiteral $userId
     */
    public function __construct(StringLiteral $userId)
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
