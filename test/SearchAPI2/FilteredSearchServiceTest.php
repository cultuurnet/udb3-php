<?php

namespace CultuurNet\UDB3\SearchAPI2;

use CultuurNet\UDB3\SearchAPI2\Filters\SearchFilterInterface;
use PHPUnit\Framework\TestCase;

class FilteredSearchServiceTest extends TestCase
{
    /**
     * @var SearchServiceInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $searchService;

    /**
     * @var FilteredSearchService
     */
    protected $filteredSearchService;

    public function SetUp()
    {
        $this->searchService = $this->createMock(
            SearchServiceInterface::class
        );

        $this->filteredSearchService = new FilteredSearchService($this->searchService);
    }

    /**
     * @test
     */
    public function it_can_add_filters_to_the_search_service()
    {
        $searchFilter = $this->createMock(SearchFilterInterface::class);

        $this->filteredSearchService->filter($searchFilter);

        $this->assertNotEmpty($this->filteredSearchService->getFilters());
    }

    /**
     * @test
     */
    public function it_should_apply_filters_to_the_parameters_when_searching()
    {
        $searchFilter = $this->createMock(SearchFilterInterface::class);
        /** @var ParameterInterface[] $searchParameters */
        $searchParameters = ['existing parameter'];

        $searchFilter
            ->method('apply')
            ->willReturn(['existing parameter', 'additional parameter']);

        $this->searchService
            ->expects($this->once())
            ->method('search')
            ->with($this->equalTo(['existing parameter', 'additional parameter']));

        $this->filteredSearchService->filter($searchFilter);
        $this->filteredSearchService->search($searchParameters);
    }
}
