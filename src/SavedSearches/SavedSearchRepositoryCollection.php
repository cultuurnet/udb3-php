<?php

namespace CultuurNet\UDB3\SavedSearches;

use CultuurNet\UDB3\SavedSearches\WriteModel\SavedSearchRepositoryInterface;
use CultuurNet\UDB3\ValueObject\SapiVersion;

class SavedSearchRepositoryCollection
{
    /**
     * @var SavedSearchRepositoryInterface
     */
    private $savedSearchRepositories;

    /**
     * @param SapiVersion $sapiVersion
     * @param SavedSearchRepositoryInterface $savedSearchRepository
     */
    public function addRepository(
        SapiVersion $sapiVersion,
        SavedSearchRepositoryInterface $savedSearchRepository
    ): void {
        $this->savedSearchRepositories[$sapiVersion->toNative()] = $savedSearchRepository;
    }

    /**
     * @param SapiVersion $sapiVersion
     * @return SavedSearchRepositoryInterface|null
     */
    public function getRepository(SapiVersion $sapiVersion): ?SavedSearchRepositoryInterface
    {
        if (!isset($this->savedSearchRepositories[$sapiVersion->toNative()])) {
            return null;
        }

        return $this->savedSearchRepositories[$sapiVersion->toNative()];
    }
}
