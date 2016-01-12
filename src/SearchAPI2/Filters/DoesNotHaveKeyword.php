<?php

namespace CultuurNet\UDB3\SearchAPI2\Filters;

use CultuurNet\Search\Parameter\FilterQuery;

abstract class DoesNotHaveKeyword implements SearchFilterInterface
{
    /**
     * @var FilterQuery
     */
    protected $filterQuery;

    public function __construct($keyword)
    {
        $keyword = (string) $keyword;

        $this->filterQuery = new FilterQuery(
            sprintf(
                '!keywords: "%s"',
                $keyword
            )
        );
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
