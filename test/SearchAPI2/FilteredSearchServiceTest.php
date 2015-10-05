<?php

namespace CultuurNet\UDB3\SearchAPI2;

use CultuurNet\UDB3\SearchAPI2\Filters\SearchFilterInterface;

class FilteredSearchServiceTest extends \PHPUnit_Framework_TestCase
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
        $this->searchService = $this->getMock(
            SearchServiceInterface::class
        );

        $this->filteredSearchService = new FilteredSearchService($this->searchService);
    }

    /**
     * @test
     */
    public function it_can_add_filters_to_the_search_service()
    {
        $searchFilter = $this->getMock(SearchFilterInterface::class);

        $this->filteredSearchService->filter($searchFilter);

        $this->assertNotEmpty($this->filteredSearchService->getFilters());
    }

    /**
     * @test
     */
    public function it_should_apply_filters_to_the_parameters_when_searching()
    {
        $searchFilter = $this->getMock(SearchFilterInterface::class);
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
