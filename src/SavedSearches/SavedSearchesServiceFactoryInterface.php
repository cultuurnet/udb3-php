<?php
/**
 * @file
 */
namespace CultuurNet\UDB3\SavedSearches;

use CultuurNet\Auth\TokenCredentials;

interface SavedSearchesServiceFactoryInterface
{
    /**
     * @param TokenCredentials $tokenCredentials
     * @return \CultureFeed_SavedSearches
     */
    public function withTokenCredentials(TokenCredentials $tokenCredentials);
}
