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

    /**
     * @test
     * @dataProvider savedSearchWriteProvider
     * @param String $userId
     * @param String $name
     * @param QueryString $query
     * @param \CultureFeed_SavedSearches_SavedSearch $savedSearch
     */
    public function it_can_write_saved_searches(
        String $userId,
        String $name,
        QueryString $query,
        \CultureFeed_SavedSearches_SavedSearch $savedSearch
    ) {
        $this->savedSearches->expects($this->once())
            ->method('subscribe')
            ->with($savedSearch);

        $this->repository->write($userId, $name, $query);
    }

    /**
     * @test
     * @dataProvider savedSearchWriteProvider
     * @param String $userId
     * @param String $name
     * @param QueryString $query
     * @param \CultureFeed_SavedSearches_SavedSearch $savedSearch
     */
    public function it_can_handle_errors_while_writing(
        String $userId,
        String $name,
        QueryString $query,
        \CultureFeed_SavedSearches_SavedSearch $savedSearch
    ) {
        $this->savedSearches->expects($this->once())
            ->method('subscribe')
            ->with($savedSearch)
            ->willThrowException(new \CultureFeed_Exception('Something went wrong.', 'UNKNOWN_ERROR'));

        $this->logger->expects($this->once())
            ->method('error')
            ->with(
                'saved_search_was_not_subscribed',
                [
                    'error' => 'Something went wrong.',
                    'userId' => (string) $userId,
                    'name' => (string) $name,
                    'query' => (string) $query,
                ]
            );

        $this->repository->write($userId, $name, $query);
    }

    /**
     * @return array
     */
    public function savedSearchWriteProvider()
    {
        $userId = new String('some-user-id');
        $name = new String('Random name for a search.');
        $query = new QueryString('city:leuven');

        $savedSearch = new \CultureFeed_SavedSearches_SavedSearch(
            $userId->toNative(),
            $name->toNative(),
            $query->toURLQueryString(),
            \CultureFeed_SavedSearches_SavedSearch::NEVER
        );

        return [
            [$userId, $name, $query, $savedSearch],
        ];
    }

    /**
     * @test
     * @dataProvider savedSearchDeleteProvider
     */
    public function it_can_delete_saved_searches(String $userId, String $searchId)
    {
        $this->savedSearches->expects($this->once())
            ->method('unsubscribe')
            ->with(
                (string) $searchId,
                (string) $userId
            );

        $this->repository->delete($userId, $searchId);
    }

    /**
     * @test
     * @dataProvider savedSearchDeleteProvider
     */
    public function it_can_handle_errors_while_deleting(String $userId, String $searchId)
    {
        $this->savedSearches->expects($this->once())
            ->method('unsubscribe')
            ->with(
                (string) $searchId,
                (string) $userId
            )
            ->willThrowException(new \CultureFeed_Exception('Something went wrong.', 'UNKNOWN_ERROR'));

        $this->logger->expects($this->once())
            ->method('error')
            ->with(
                'User was not unsubscribed from saved search.',
                [
                    'error' => 'Something went wrong.',
                    'userId' => (string) $userId,
                    'searchId' => (string) $searchId,
                ]
            );

        $this->repository->delete($userId, $searchId);
    }

    /**
     * @return array
     */
    public function savedSearchDeleteProvider()
    {
        return [
            [
                new String('some-user-id'),
                new String('some-search-id'),
            ],
        ];
    }
}
