<?php

namespace CultuurNet\UDB3\SavedSearches\Properties;

use ValueObjects\Web\EmailAddress;

class CreatedByQueryString extends QueryString
{
    /**
     * @var EmailAddress
     */
    protected $emailAddress;

    /**
     * @param StringLiteral $userId
     */
    public function __construct(EmailAddress $emailAddress)
    {
        $this->emailAddress = $emailAddress;
        parent::__construct($this->generateQuery());
    }

    /**
     * @return string
     */
    private function generateQuery()
    {
        return 'createdby:' . $this->emailAddress;
    }
}
