<?php

namespace CultuurNet\UDB3\SearchAPI2\Filters;

use CultuurNet\Search\Parameter\FilterQuery;

class UDB3Place implements SearchFilterInterface
{
    /**
     * @var FilterQuery
     */
    protected $filterQuery;

    public function __construct()
    {
        $this->filterQuery = new FilterQuery('!keywords: "udb3 place"');
    }

    /**
     * {@inheritdoc}
     */
    public function apply(array $params)
    {
        $params[] = $this->filterQuery;
        return $params;
    }
}
