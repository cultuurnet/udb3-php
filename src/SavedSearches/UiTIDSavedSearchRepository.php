<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\SavedSearches;

use CultuurNet\UDB3\SavedSearches\Properties\QueryString;
use CultuurNet\UDB3\SavedSearches\ReadModel\SavedSearch;
use CultuurNet\UDB3\SavedSearches\ReadModel\SavedSearchRepositoryInterface as SavedSearchReadModelRepositoryInterface;
use CultuurNet\UDB3\SavedSearches\WriteModel\SavedSearchRepositoryInterface as SavedSearchWriteModelRepositoryInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use ValueObjects\String\String;

/**
 * Implementation of a SavedSearchRepository on top of the UiTID saved searches
 * API.
 */
class UiTIDSavedSearchRepository implements
    SavedSearchReadModelRepositoryInterface,
    SavedSearchWriteModelRepositoryInterface,
    LoggerAwareInterface
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

    /**
     * {@inheritdoc}
     */
    public function write(String $userId, String $name, QueryString $queryString)
    {
        $userId = (string) $userId;
        $name = (string) $name;
        $query = $queryString->toURLQueryString();

        $savedSearch = new \CultureFeed_SavedSearches_SavedSearch(
            $userId,
            $name,
            $query,
            \CultureFeed_SavedSearches_SavedSearch::NEVER
        );

        try {
            $this->savedSearches->subscribe($savedSearch);
        } catch (\Exception $exception) {
            if ($this->logger) {
                $this->logger->error(
                    'saved_search_was_not_subscribed',
                    [
                        'error' => $exception->getMessage(),
                        'userId' => $userId,
                        'name' => $name,
                        'query' => (string) $queryString,
                    ]
                );
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function delete(String $userId, String $searchId)
    {
        $userId = (string) $userId;
        $searchId = (string) $searchId;

        try {
            $this->savedSearches->unsubscribe($searchId, $userId);
        } catch (\Exception $exception) {
            if ($this->logger) {
                $this->logger->error(
                    'User was not unsubscribed from saved search.',
                    [
                        'error' => $exception->getMessage(),
                        'userId' => $userId,
                        'searchId' => $searchId,
                    ]
                );
            }
        }
    }
}
