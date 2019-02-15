<?php

namespace CultuurNet\UDB3\SavedSearches;

use CultuurNet\UDB3\SavedSearches\Properties\CreatedByQueryString;
use CultuurNet\UDB3\SavedSearches\Properties\CreatorQueryString;
use CultuurNet\UDB3\SavedSearches\ReadModel\SavedSearch;
use CultuurNet\UDB3\SavedSearches\ReadModel\SavedSearchRepositoryInterface;
use CultuurNet\UDB3\SavedSearches\ValueObject\CreatedByQueryMode;
use CultuurNet\UDB3\ValueObject\SapiVersion;
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
     * @var SapiVersion
     */
    private $sapiVersion;

    /**
     * @param \CultureFeed_User $user
     * @param CreatedByQueryMode $createdByQueryMode
     */
    public function __construct(
        \CultureFeed_User $user,
        CreatedByQueryMode $createdByQueryMode,
        SapiVersion $sapiVersion
    ) {
        $this->user = $user;
        $this->createdByQueryMode = $createdByQueryMode;
        $this->sapiVersion = $sapiVersion;
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

        if ($this->sapiVersion === SapiVersion::V2()) {
            switch ($this->createdByQueryMode->toNative()) {
                case CreatedByQueryMode::EMAIL:
                    $createdByQueryString = new CreatedByQueryString(
                        $this->user->mbox
                    );
                    break;
                case CreatedByQueryMode::MIXED:
                    $createdByQueryString = new CreatedByQueryString(
                        $this->user->mbox,
                        $this->user->id
                    );
                    break;
                default:
                    $createdByQueryString = new CreatedByQueryString(
                        $this->user->id
                    );
            }
        } else {
            switch ($this->createdByQueryMode->toNative()) {
                case CreatedByQueryMode::EMAIL:
                    $createdByQueryString = new CreatorQueryString(
                        $this->user->mbox
                    );
                    break;
                case CreatedByQueryMode::MIXED:
                    $createdByQueryString = new CreatorQueryString(
                        $this->user->mbox,
                        $this->user->id
                    );
                    break;
                default:
                    $createdByQueryString = new CreatorQueryString(
                        $this->user->id
                    );
            }
        }

        return new SavedSearch($name, $createdByQueryString);
    }
}
