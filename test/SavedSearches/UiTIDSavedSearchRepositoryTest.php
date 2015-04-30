<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\SavedSearches;

use CultuurNet\UDB3\SavedSearches\Properties\QueryString;
use CultuurNet\UDB3\SavedSearches\ReadModel\SavedSearch;
use Psr\Log\LoggerInterface;
use ValueObjects\String\String;

class UiTIDSavedSearchRepositoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \CultureFeed_SavedSearches|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $savedSearches;

    /**
     * @var LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $logger;

    /**
     * @var UiTIDSavedSearchRepository
     */
    protected $repository;

    public function setUp()
    {
        $this->savedSearches = $this->getMock(
            \CultureFeed_SavedSearches::class
        );

        $this->repository = new UiTIDSavedSearchRepository(
            $this->savedSearches
        );

        $this->logger = $this->getMock(LoggerInterface::class);
        $this->repository->setLogger($this->logger);
    }

    /**
     * @test
     */
    public function it_can_retrieve_saved_searches_owned_by_current_user()
    {
        $this->savedSearches->expects($this->once())
            ->method('getList')
            ->with()
            ->willReturn(
                [
                    100 => new \CultureFeed_SavedSearches_SavedSearch(
                        'abc',
                        'In Leuven',
                        'q=city%3A%22Leuven%22',
                        \CultureFeed_SavedSearches_SavedSearch::DAILY,
                        '100'
                    ),
                    200 => new \CultureFeed_SavedSearches_SavedSearch(
                        'abc',
                        'In Herent',
                        'q=city%3A%22Herent%22',
                        \CultureFeed_SavedSearches_SavedSearch::WEEKLY,
                        '101'
                    ),
                ]
            );

        $searches = $this->repository->ownedByCurrentUser();

        $this->assertEquals(
            [
                new SavedSearch(
                    new String('In Leuven'),
                    new QueryString('city:"Leuven"'),
                    new String('100')
                ),
                new SavedSearch(
                    new String('In Herent'),
                    new QueryString('city:"Herent"'),
                    new String('101')
                ),
            ],
            $searches
        );
    }

    /**
     * @test
     */
    public function it_can_handle_saved_searches_with_an_incorrect_query()
    {
        $this->savedSearches->expects($this->once())
            ->method('getList')
            ->with()
            ->willReturn(
                [
                    100 => new \CultureFeed_SavedSearches_SavedSearch(
                        'abc',
                        'In Leuven',
                        'city%3A%22Leuven%22',
                        \CultureFeed_SavedSearches_SavedSearch::DAILY,
                        '100'
                    ),
                ]
            );

        $this->logger->expects($this->once())
            ->method('error')
            ->with(
                'Omitted saved search from list of current user because it was invalid.',
                [
                    'message' => 'Provided query string should contain a parameter named "q".',
                    'name' => 'In Leuven',
                    'query' => 'city%3A%22Leuven%22',
                    'id' => '100',
                    'userId' => 'abc',
                ]
            );

        $this->repository->ownedByCurrentUser();
    }
}
