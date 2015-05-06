<?php

namespace CultuurNet\UDB3\SavedSearches;

use CultuurNet\UDB3\SavedSearches\Properties\CreatedByQueryString;
use CultuurNet\UDB3\SavedSearches\ReadModel\SavedSearch;
use CultuurNet\UDB3\SavedSearches\ReadModel\SavedSearchRepositoryInterface;
use ValueObjects\String\String;
use ValueObjects\Web\EmailAddress;

class FixedSavedSearchRepository implements SavedSearchRepositoryInterface
{
    /**
     * @var \CultureFeed_User
     */
    protected $user;

    /**
     * @param String $userId
     */
    public function __construct(\CultureFeed_User $user)
    {
        $this->user = $user;
        $this->emailAddress = new EmailAddress($user->mbox);
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
        $name = new String('Door mij ingevoerd');
        $query = new CreatedByQueryString($this->emailAddress);

        return new SavedSearch($name, $query);
    }
}
