<?php

namespace CultuurNet\UDB3\Search;

use CultuurNet\UDB3\Offer\IriOfferIdentifier;
use CultuurNet\UDB3\Offer\OfferIdentifierCollection;
use CultuurNet\UDB3\Offer\OfferType;
use Psr\Log\LoggerInterface;
use ValueObjects\Number\Integer;

class ResultsGeneratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SearchServiceInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $searchService;

    /**
     * @var string
     */
    private $sorting;

    /**
     * @var int
     */
    private $pageSize;

    /**
     * @var ResultsGenerator
     */
    private $generator;

    /**
     * @var LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $logger;

    /**
     * @var string
     */
    private $query;

    public function setUp()
    {
        $this->searchService = $this->getMock(SearchServiceInterface::class);

        $this->sorting = ResultsGenerator::SORT_CREATION_DATE_ASC;
        $this->pageSize = 2;

        $this->generator = new ResultsGenerator(
            $this->searchService,
            $this->sorting,
            $this->pageSize
        );

        $this->logger = $this->getMock(LoggerInterface::class);

        $this->generator->setLogger($this->logger);

        $this->query = 'city:leuven';
    }

    /**
     * @test
     */
    public function it_has_configurable_sorting_and_page_size_with_default_values()
    {
        $generator = new ResultsGenerator($this->searchService);

        $this->assertEquals(ResultsGenerator::SORT_CREATION_DATE_ASC, $generator->getSorting());
        $this->assertEquals(10, $generator->getPageSize());

        /* @var ResultsGenerator $generator */
        $generator = $generator->withSorting('creationdate desc')
            ->withPageSize(5);

        $this->assertEquals('creationdate desc', $generator->getSorting());
        $this->assertEquals(5, $generator->getPageSize());
    }

    /**
     * @test
     *
     * @dataProvider pagedResultsDataProvider
     *
     * @param int $givenPageSize
     *   Number of results per page.
     *
     * @param array $givenPages
     *   Multiple pages with results per page.
     *
     * @param array $expectedResults
     *   All results in a single array.
     *
     * @param array $expectedLogs
     *   All expected logs in a single array.
     */
    public function it_loops_over_all_pages_and_yields_each_unique_result_while_logging_duplicates(
        $givenPageSize,
        $givenPages,
        $expectedResults,
        $expectedLogs = []
    ) {
        $currentPage = 0;
        $totalPages = count($givenPages);
        $totalResults = count($expectedResults);
        $actualResults = [];
        $actualLogs = [];

        $this->searchService->expects($this->exactly($totalPages))
            ->method('search')
            ->willReturnCallback(
                function (
                    $query,
                    $pageSize,
                    $start,
                    $sorting
                ) use (
                    $givenPageSize,
                    $givenPages,
                    $totalResults,
                    &$currentPage
                ) {
                    // Do some assertions on the provided arguments here.
                    // We can't use withConsecutive() on the mock object
                    // because we have a variable number of method calls and
                    // withConsecutive() doesn't allow an array of arguments.
                    $this->assertEquals($this->query, $query);
                    $this->assertEquals($givenPageSize, $pageSize);
                    $this->assertEquals($givenPageSize * $currentPage, $start);
                    $this->assertEquals($this->sorting, $sorting);

                    $pageResults = $givenPages[$currentPage];

                    $currentPage++;

                    return new Results(
                        OfferIdentifierCollection::fromArray($pageResults),
                        new Integer($totalResults)
                    );
                }
            );

        $this->logger->expects($this->any())
            ->method('error')
            ->willReturnCallback(
                function ($type, $data) use (&$actualLogs) {
                    $this->assertEquals('query_duplicate_event', $type);

                    $error = $data['error'];
                    $actualLogs[] = $error;
                }
            );

        $generator = $this->generator->withPageSize($givenPageSize);
        foreach ($generator->search($this->query) as $result) {
            $actualResults[] = $result;
        }

        $this->assertEquals($expectedResults, $actualResults);
        $this->assertEquals($expectedLogs, $actualLogs);
    }

    /**
     * @return array
     */
    public function pagedResultsDataProvider()
    {
        $event1 = new IriOfferIdentifier(
            'event/1',
            OfferType::EVENT()
        );

        $event2 = new IriOfferIdentifier(
            'event/2',
            OfferType::EVENT()
        );

        $event3 = new IriOfferIdentifier(
            'event/3',
            OfferType::EVENT()
        );

        $event4 = new IriOfferIdentifier(
            'event/4',
            OfferType::EVENT()
        );

        $event5 = new IriOfferIdentifier(
            'event/5',
            OfferType::EVENT()
        );

        $event6 = new IriOfferIdentifier(
            'event/6',
            OfferType::EVENT()
        );

        $event7 = new IriOfferIdentifier(
            'event/7',
            OfferType::EVENT()
        );

        return [
            [
                // 2 results per page, 1 result.
                2,
                [
                    [
                        $event1,
                    ],
                ],
                [
                    $event1,
                ],
            ],
            [
                // 2 results per page, 2 results.
                2,
                [
                    [
                        $event1,
                        $event2,
                    ],
                ],
                [
                    $event1,
                    $event2,
                ],
            ],
            [
                // 2 results per page, 5 results.
                2,
                [
                    [
                        $event1,
                        $event2,
                    ],
                    [
                        $event3,
                        $event4,
                    ],
                    [
                        $event5,
                    ],
                ],
                [
                    $event1,
                    $event2,
                    $event3,
                    $event4,
                    $event5,
                ],
            ],
            [
                // 2 results per page, 6 results.
                2,
                [
                    [
                        $event1,
                        $event2,
                    ],
                    [
                        $event3,
                        $event4,
                    ],
                    [
                        $event5,
                        $event6,
                    ],
                ],
                [
                    $event1,
                    $event2,
                    $event3,
                    $event4,
                    $event5,
                    $event6,
                ],
            ],
            [
                // 3 results per page, 5 results.
                3,
                [
                    [
                        $event1,
                        $event2,
                        $event3,
                    ],
                    [
                        $event4,
                        $event5,
                    ]
                ],
                [
                    $event1,
                    $event2,
                    $event3,
                    $event4,
                    $event5,
                ],
            ],
            [
                // 3 results per page, 6 results.
                3,
                [
                    [
                        $event1,
                        $event2,
                        $event3,
                    ],
                    [
                        $event4,
                        $event5,
                        $event6,
                    ],
                ],
                [
                    $event1,
                    $event2,
                    $event3,
                    $event4,
                    $event5,
                    $event6,
                ],
            ],
            [
                // 5 results per page, 6 results.
                5,
                [
                    [
                        $event1,
                        $event2,
                        $event3,
                        $event4,
                        $event5,
                    ],
                    [
                        $event6,
                    ]
                ],
                [
                    $event1,
                    $event2,
                    $event3,
                    $event4,
                    $event5,
                    $event6,
                ],
            ],
            [
                // 3 results per page, 9 results, 2 duplicates.
                3,
                [
                    [
                        $event1,
                        $event2,
                        $event1,
                    ],
                    [
                        $event3,
                        $event4,
                        $event5,
                    ],
                    [
                        $event4,
                        $event6,
                        $event7,
                    ]
                ],
                [
                    $event1,
                    $event2,
                    $event3,
                    $event4,
                    $event5,
                    $event6,
                    $event7,
                ],
                [
                    "Found duplicate offer 1 on page 0, occurred first time on page 0.",
                    "Found duplicate offer 4 on page 2, occurred first time on page 1.",
                ],
            ],
        ];
    }
}
