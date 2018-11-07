<?php

namespace CultuurNet\UDB3\SavedSearches\Command;

use CultuurNet\UDB3\ValueObject\SapiVersion;
use ValueObjects\StringLiteral\StringLiteral;

abstract class SavedSearchCommand
{
    /**
     * @var SapiVersion
     */
    protected $sapiVersion;

    /**
     * @var StringLiteral
     */
    protected $userId;

    /**
     * @param SapiVersion $sapiVersion
     * @param StringLiteral $userId
     */
    public function __construct(
        SapiVersion $sapiVersion,
        StringLiteral $userId
    ) {
        $this->sapiVersion = $sapiVersion;
        $this->userId = $userId;
    }

    /**
     * @return SapiVersion
     */
    public function getSapiVersion(): SapiVersion
    {
        return $this->sapiVersion;
    }

    /**
     * @return StringLiteral
     */
    public function getUserId()
    {
        return $this->userId;
    }
}
