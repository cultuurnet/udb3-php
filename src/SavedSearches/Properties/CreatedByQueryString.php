<?php

namespace CultuurNet\UDB3\SavedSearches\Properties;

use CultuurNet\UDB3\SavedSearches\ValueObject\CreatedByQueryMode;
use CultuurNet\UDB3\SavedSearches\ValueObject\UserId;
use ValueObjects\Web\EmailAddress;

class CreatedByQueryString extends QueryString
{
    /**
     * @var UserId
     */
    protected $userId;

    /**
     * @var EmailAddress
     */
    protected $emailAddress;

    /**
     * @var CreatedByQueryMode
     */
    protected $createdByQueryMode;

    /**
     * @param UserId $userId
     * @param EmailAddress $emailAddress
     * @param CreatedByQueryMode $createdByQueryMode
     */
    public function __construct(
        UserId $userId,
        EmailAddress $emailAddress,
        CreatedByQueryMode $createdByQueryMode
    ) {
        $this->userId = $userId;
        $this->emailAddress = $emailAddress;
        $this->createdByQueryMode = $createdByQueryMode;

        parent::__construct($this->generateQuery());
    }

    /**
     * @return string
     */
    private function generateQuery(): string
    {
        switch ($this->createdByQueryMode->toNative()) {
            case CreatedByQueryMode::EMAIL:
                $creator = $this->emailAddress->toNative();
                break;
            case CreatedByQueryMode::MIXED:
                $uuid = $this->userId->toNative();
                $email = $this->emailAddress->toNative();
                $creator = '('. $uuid . ' OR ' . $email . ')';
                break;
            default:
                $creator = $this->userId->toNative();
        }

        return 'createdby:' . $creator;
    }
}
