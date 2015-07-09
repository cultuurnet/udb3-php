<?php

namespace CultuurNet\UDB3\SearchAPI2;

abstract class SearchServiceDecorator implements SearchServiceInterface
{
    /**
     * @var SearchServiceInterface
     */
    protected $decoratedSearchService;

    function __construct(SearchServiceInterface $decoratedSearchService)
    {
        $this->decoratedSearchService = $decoratedSearchService;
    }
}
