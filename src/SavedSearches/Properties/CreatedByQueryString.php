<?php

namespace CultuurNet\UDB3\SavedSearches\Properties;

use CultuurNet\UDB3\SavedSearches\ValueObject\UserId;

class CreatedByQueryString extends QueryString
{
    /**
     * @var UserId
     */
    protected $userId;

    /**
     * @param UserId $userId
     */
    public function __construct(UserId $userId)
    {
        $this->userId = $userId;
        parent::__construct($this->generateQuery());
    }

    /**
     * @return string
     */
    private function generateQuery(): string
    {
        return 'createdby:' . $this->userId->toNative();
    }
}
