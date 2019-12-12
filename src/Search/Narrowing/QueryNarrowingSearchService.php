<?php

namespace CultuurNet\UDB3\Search\Narrowing;

use CultuurNet\UDB3\Search\SearchServiceInterface;

class QueryNarrowingSearchService implements SearchServiceInterface
{
    /**
     * @var SearchServiceInterface
     */
    private $searchService;

    /**
     * @var QueryNarrowerInterface
     */
    private $queryNarrower;

    /**
     * QueryNarrowingSearchService constructor.
     *
     * @param SearchServiceInterface $searchService
     * @param QueryNarrowerInterface $queryNarrower
     */
    public function __construct(SearchServiceInterface $searchService, QueryNarrowerInterface $queryNarrower)
    {
        $this->searchService = $searchService;
        $this->queryNarrower = $queryNarrower;
    }

    /**
     * @inheritdoc
     */
    public function search(string $query, $limit = 30, $start = 0, array $sort = null)
    {
        $narrowedQuery = $this->queryNarrower->narrow($query);

        return $this->searchService->search($narrowedQuery, $limit, $start, $sort);
    }
}
