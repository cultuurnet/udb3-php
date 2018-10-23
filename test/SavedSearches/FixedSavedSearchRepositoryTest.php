<?php

namespace CultuurNet\UDB3\SavedSearches;

use CultuurNet\UDB3\SavedSearches\Properties\CreatedByQueryString;
use CultuurNet\UDB3\SavedSearches\ReadModel\SavedSearch;
use CultuurNet\UDB3\SavedSearches\ValueObject\UserId;
use ValueObjects\StringLiteral\StringLiteral;

class FixedSavedSearchRepositoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \CultureFeed_User
     */
    protected $user;

    /**
     * @var FixedSavedSearchRepository
     */
    protected $repository;

    public function setUp()
    {
        $this->user = new \CultureFeed_User();
        $this->user->id = 'cef70b98-2d4d-40a9-95f0-762aae66ef3f';

        $this->repository = new FixedSavedSearchRepository($this->user);
    }

    /**
     * @test
     */
    public function it_contains_a_search_of_all_events_created_by_current_user()
    {
        $name = new StringLiteral('Door mij ingevoerd');

        $userId = new UserId($this->user->id);
        $query = new CreatedByQueryString($userId);

        $savedSearch = new SavedSearch($name, $query);

        $this->assertRepositoryContains($savedSearch);
    }

    /**
     * The assertContains method is too strict when comparing objects in
     * arrays, so we use in_array() instead.
     *
     * @param SavedSearch $savedSearch
     */
    private function assertRepositoryContains(SavedSearch $savedSearch)
    {
        $savedSearches = $this->repository->ownedByCurrentUser();
        $this->assertTrue(in_array($savedSearch, $savedSearches));
    }
}
