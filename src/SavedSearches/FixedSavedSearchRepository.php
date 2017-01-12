<?php

namespace CultuurNet\UDB3\SavedSearches;

use CultuurNet\UDB3\SavedSearches\Properties\CreatedByQueryString;
use CultuurNet\UDB3\SavedSearches\ReadModel\SavedSearch;
use CultuurNet\UDB3\SavedSearches\ReadModel\SavedSearchRepositoryInterface;
use ValueObjects\StringLiteral\StringLiteral;
use ValueObjects\Web\EmailAddress;

class FixedSavedSearchRepository implements SavedSearchRepositoryInterface
{
    /**
     * @var \CultureFeed_User
     */
    protected $user;

    /**
     * @param StringLiteral $userId
     */
    public function __construct(\CultureFeed_User $user)
    {
        $this->user = $user;
    }

    /**
     * @return SavedSearch[]
     */
    public function ownedByCurrentUser()
    {
        return [
            $this->getCreatedByCurrentUserSearch(),
        ];
    }

    /**
     * @return SavedSearch
     */
    protected function getCreatedByCurrentUserSearch()
    {
        $name = new StringLiteral('Door mij ingevoerd');
        $emailAddress = new EmailAddress($this->user->mbox);
        $query = new CreatedByQueryString($emailAddress);

        return new SavedSearch($name, $query);
    }
}
