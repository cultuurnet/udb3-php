<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Search\Cache;

interface CacheHandlerInterface
{
    /**
     * @return void
     */
    public function warmUpCache();

    /**
     * @return void
     */
    public function clearCache();
}
