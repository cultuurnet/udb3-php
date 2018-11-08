<?php

namespace CultuurNet\UDB3\SavedSearches;

use CultuurNet\UDB3\SavedSearches\WriteModel\SavedSearchRepositoryInterface;
use CultuurNet\UDB3\ValueObject\SapiVersion;

class SavedSearchRepositoryCollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_can_store_saved_search_repositories(): void
    {
        $savedSearchRepositoryCollection = new SavedSearchRepositoryCollection();

        $sapi2SavedSearchRepository = $this->createMock(SavedSearchRepositoryInterface::class);
        $sapi3SavedSearchRepository = $this->createMock(SavedSearchRepositoryInterface::class);

        $savedSearchRepositoryCollection = $savedSearchRepositoryCollection->withRepository(
            new SapiVersion(SapiVersion::V2),
            $sapi2SavedSearchRepository
        );

        $savedSearchRepositoryCollection = $savedSearchRepositoryCollection->withRepository(
            new SapiVersion(SapiVersion::V3),
            $sapi3SavedSearchRepository
        );

        $this->assertEquals(
            $sapi2SavedSearchRepository,
            $savedSearchRepositoryCollection->getRepository(
                new SapiVersion(SapiVersion::V2)
            )
        );

        $this->assertEquals(
            $sapi3SavedSearchRepository,
            $savedSearchRepositoryCollection->getRepository(
                new SapiVersion(SapiVersion::V3)
            )
        );
    }

    /**
     * @test
     */
    public function it_return_null_when_repo_not_found_for_given_sapi_version(): void
    {
        $savedSearchRepositoryCollection = new SavedSearchRepositoryCollection();

        $sapi2SavedSearchRepository = $this->createMock(SavedSearchRepositoryInterface::class);

        $savedSearchRepositoryCollection->withRepository(
            new SapiVersion(SapiVersion::V2),
            $sapi2SavedSearchRepository
        );

        $this->assertNull(
            $savedSearchRepositoryCollection->getRepository(new SapiVersion(SapiVersion::V3))
        );
    }
}
