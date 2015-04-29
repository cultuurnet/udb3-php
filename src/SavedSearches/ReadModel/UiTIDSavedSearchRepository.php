<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\SavedSearches\ReadModel;

use CultuurNet\UDB3\SavedSearches\Properties\QueryString;
use ValueObjects\String\String;

/**
 * Implementation of a SavedSearchRepository on top of the UiTID saved searches
 * API.
 */
class UiTIDSavedSearchRepository implements SavedSearchRepositoryInterface
{
    /**
     * @var \CultureFeed_SavedSearches
     */
    protected $savedSearches;

    public function __construct(\CultureFeed_SavedSearches $savedSearches)
    {
        $this->savedSearches = $savedSearches;
    }

    /**
     * @return SavedSearch[]
     */
    public function ownedByCurrentUser()
    {
        $searches = array_values($this->savedSearches->getList());

        return array_map(
            function (\CultureFeed_SavedSearches_SavedSearch $item) {
                return $this->createSavedSearchForRead($item);
            },
            $searches
        );
    }

    /**
     * @param \CultureFeed_SavedSearches_SavedSearch $savedSearch
     * @return SavedSearch
     */
    private function createSavedSearchForRead(\CultureFeed_SavedSearches_SavedSearch $savedSearch)
    {
        $name = new String($savedSearch->name);
        $query = QueryString::fromURLQueryString($savedSearch->query);
        $id = new String($savedSearch->id);

        return new SavedSearch($name, $query, $id);
    }
}
