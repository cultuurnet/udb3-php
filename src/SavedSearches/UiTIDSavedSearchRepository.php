<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\SavedSearches;

use CultuurNet\UDB3\SavedSearches\Properties\QueryString;
use CultuurNet\UDB3\SavedSearches\ReadModel\SavedSearch;
use CultuurNet\UDB3\SavedSearches\ReadModel\SavedSearchRepositoryInterface as SavedSearchReadModelRepositoryInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use ValueObjects\String\String;

/**
 * Implementation of a SavedSearchRepository on top of the UiTID saved searches
 * API.
 */
class UiTIDSavedSearchRepository implements SavedSearchReadModelRepositoryInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @var \CultureFeed_SavedSearches
     */
    protected $savedSearches;

    /**
     * @param \CultureFeed_SavedSearches $savedSearches
     */
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

        foreach ($searches as $index => $search) {
            try {
                $searches[$index] = $this->createSavedSearchForRead($search);
            } catch (\InvalidArgumentException $e) {
                unset($searches[$index]);

                if ($this->logger) {
                    $this->logger->error(
                        'Omitted saved search from list of current user because it was invalid.',
                        [
                            'message' => $e->getMessage(),
                            'name' => $search->name,
                            'query' => $search->query,
                            'id' => $search->id,
                            'userId' => $search->userId,
                        ]
                    );
                }
            }
        }

        return array_values($searches);
    }

    /**
     * @param \CultureFeed_SavedSearches_SavedSearch $savedSearch
     * @return SavedSearch
     */
    private function createSavedSearchForRead(\CultureFeed_SavedSearches_SavedSearch $savedSearch)
    {
        $name = new String($savedSearch->name);
        $query = QueryString::fromURLQueryString($savedSearch->query);
        $id = new String((string) $savedSearch->id);

        return new SavedSearch($name, $query, $id);
    }
}
