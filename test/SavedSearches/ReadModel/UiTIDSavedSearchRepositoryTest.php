<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\SavedSearches\ReadModel;

class UiTIDSavedSearchRepositoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \CultureFeed_SavedSearches|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $savedSearches;

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
                    'In Leuven',
                    'city:"Leuven"',
                    '100'
                ),
                new SavedSearch(
                    'In Herent',
                    'city:"Herent"',
                    '101'
                ),
            ],
            $searches
        );
    }
}
