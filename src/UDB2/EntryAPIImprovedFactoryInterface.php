<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\UDB2;

use CultuurNet\Auth\TokenCredentials;
use CultuurNet\Entry\EntryAPI;

/**
 * @param TokenCredentials $tokenCredentials
 * @return EntryAPI
 */
interface EntryAPIImprovedFactoryInterface
{
    public function withTokenCredentials(TokenCredentials $tokenCredentials);
}
