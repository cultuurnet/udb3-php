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
     * @return SavedSearchRepositoryCollection
     */
    public function withRepository(
        SapiVersion $sapiVersion,
        SavedSearchRepositoryInterface $savedSearchRepository
    ): SavedSearchRepositoryCollection {
        $c = clone $this;
        $c->savedSearchRepositories[$sapiVersion->toNative()] = $savedSearchRepository;
        return $c;
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
