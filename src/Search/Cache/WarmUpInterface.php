<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Search\Cache;

interface WarmUpInterface
{
    /**
     * @return void
     */
    public function warmUpCache();
}
