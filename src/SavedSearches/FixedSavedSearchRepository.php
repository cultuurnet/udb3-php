<?php

namespace CultuurNet\UDB3\SavedSearches;

use CultuurNet\UDB3\SavedSearches\Properties\CreatedByQueryString;
use CultuurNet\UDB3\SavedSearches\ReadModel\SavedSearch;
use CultuurNet\UDB3\SavedSearches\ReadModel\SavedSearchRepositoryInterface;
use CultuurNet\UDB3\SavedSearches\ValueObject\CreatedByQueryMode;
use ValueObjects\StringLiteral\StringLiteral;

class FixedSavedSearchRepository implements SavedSearchRepositoryInterface
{
    /**
     * @var \CultureFeed_User
     */
    private $user;

    /**
     * @var CreatedByQueryMode
     */
    protected $createdByQueryMode;

    /**
     * @param \CultureFeed_User $user
     * @param CreatedByQueryMode $createdByQueryMode
     */
    public function __construct(
        \CultureFeed_User $user,
        CreatedByQueryMode $createdByQueryMode
    ) {
        $this->user = $user;
        $this->createdByQueryMode = $createdByQueryMode;
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

        $queryParts = [];
        switch ($this->createdByQueryMode->toNative()) {
            case CreatedByQueryMode::EMAIL:
                $queryParts[] = $this->user->mbox;
                break;
            case CreatedByQueryMode::MIXED:
                $queryParts[] = $this->user->mbox;
                $queryParts[] = $this->user->id;
                break;
            default:
                $queryParts[] = $this->user->id;
        }

        return new SavedSearch($name, new CreatedByQueryString($queryParts));
    }
}
