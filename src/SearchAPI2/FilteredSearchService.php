<?php

namespace CultuurNet\UDB3\SearchAPI2;

use CultuurNet\UDB3\SearchAPI2\Filters\SearchFilterInterface;

class FilteredSearchService implements SearchServiceInterface
{
    /**
     * @var SearchServiceInterface
     */
    protected $filteredSearchService;

    /**
     * @var SearchFilterInterface[]
     */
    protected $filters;

    /**
     * Wraps a search service so it can be filtered
     *
     * @param SearchServiceInterface $searchService
     */
    public function __construct(SearchServiceInterface $searchService)
    {
        $this->filteredSearchService = $searchService;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function search(array $params)
    {
        foreach ($this->filters as $filter) {
            $params = $filter->apply($params);
        }

        return $this->filteredSearchService->search($params);
    }

    /**
     * @param SearchFilterInterface $filter
     */
    public function filter(SearchFilterInterface $filter)
    {
        $this->filters[] = $filter;
    }

    /**
     * @return SearchFilterInterface[]
     */
    public function getFilters()
    {
        return $this->filters;
    }
}
