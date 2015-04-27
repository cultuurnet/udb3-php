<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\SavedSearches\ReadModel;

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
        parse_str($savedSearch->query, $query);

        return new SavedSearch($savedSearch->name, $query['q'], $savedSearch->id);
    }
}
