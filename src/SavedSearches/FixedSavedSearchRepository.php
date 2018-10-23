<?php

namespace CultuurNet\UDB3\SavedSearches;

use CultuurNet\UDB3\SavedSearches\Properties\CreatedByQueryString;
use CultuurNet\UDB3\SavedSearches\ReadModel\SavedSearch;
use CultuurNet\UDB3\SavedSearches\ReadModel\SavedSearchRepositoryInterface;
use CultuurNet\UDB3\SavedSearches\ValueObject\UserId;
use ValueObjects\StringLiteral\StringLiteral;

class FixedSavedSearchRepository implements SavedSearchRepositoryInterface
{
    /**
     * @var \CultureFeed_User
     */
    protected $user;

    /**
     * @param \CultureFeed_User $user
     */
    public function __construct(\CultureFeed_User $user)
    {
        $this->user = $user;
    }

    /**
     * @return SavedSearch[]
     */
    public function ownedByCurrentUser(): array
    {
        return [
            $this->getCreatedByCurrentUserSearch(),
        ];
    }

    /**
     * @return SavedSearch
     */
    protected function getCreatedByCurrentUserSearch(): SavedSearch
    {
        $name = new StringLiteral('Door mij ingevoerd');
        $userId = new UserId($this->user->id);
        $query = new CreatedByQueryString($userId);

        return new SavedSearch($name, $query);
    }
}
