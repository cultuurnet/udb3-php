<?php

namespace CultuurNet\UDB3\SavedSearches\Properties;

use ValueObjects\String\String;

class CreatedByQueryString extends QueryString
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
        parent::__construct($this->generateQuery());
    }

    /**
     * @return string
     */
    private function generateQuery()
    {
        return 'createdby:' . $this->userId;
    }
}
