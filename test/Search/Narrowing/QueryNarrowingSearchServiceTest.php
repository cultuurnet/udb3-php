<?php

namespace CultuurNet\UDB3\Search\Narrowing;

use CultuurNet\UDB3\Offer\OfferIdentifierCollection;
use CultuurNet\UDB3\Search\Results;
use CultuurNet\UDB3\Search\SearchServiceInterface;
use PHPUnit\Framework\TestCase;
use ValueObjects\Number\Integer;

class QueryNarrowingSearchServiceTest extends TestCase
{
    /**
     * @var SearchServiceInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $wrappedSearchService;

    /**
     * @var QueryNarrowingSearchService
     */
    private $searchService;

    /**
     * @var QueryNarrowerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $queryNarrower;

    public function setUp()
    {
        $this->queryNarrower = $this->getMockBuilder(QueryNarrowerInterface::class)->getMock();

        $this->wrappedSearchService = $this->getMockBuilder(SearchServiceInterface::class)->getMock();

        $this->searchService = new QueryNarrowingSearchService($this->wrappedSearchService, $this->queryNarrower);
    }

    /**
     * @test
     */
    public function itCallsTheWrappedSearchServiceWithTheNarrowedQuery()
    {
        $query = 'address.\*.postalCode:3000';
        $narrowedQuery = '(address.\*.postalCode:3000) AND workflowStatus:READY_FOR_VALIDATION';

        $limit = 20;
        $start = 40;
        $sort = 'title ASC';

        $results = new Results(new OfferIdentifierCollection(), new Integer(0));

        $this->queryNarrower->expects($this->once())
            ->method('narrow')
            ->with($query)
            ->willReturn($narrowedQuery);

        $this->wrappedSearchService->expects($this->once())
            ->method('search')
            ->with(
                $narrowedQuery,
                $limit,
                $start,
                $sort
            )
            ->willReturn($results);

        $actualResults = $this->searchService->search($query, $limit, $start, $sort);

        $this->assertSame(
            $results,
            $actualResults
        );
    }
}
