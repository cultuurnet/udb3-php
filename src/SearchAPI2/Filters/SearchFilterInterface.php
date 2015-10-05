<?php

namespace CultuurNet\UDB3\SearchAPI2\Filters;

use CultuurNet\Search\Parameter\ParameterInterface;

interface SearchFilterInterface
{
    /**
     * @param ParameterInterface[] $searchParameters
     * @return ParameterInterface[]
     */
    public function apply(array $searchParameters);
}
