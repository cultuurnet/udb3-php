<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Hydra;

interface PageUrlGenerator
{
    /**
     * @param int $pageNumber
     * @return string
     */
    public function urlForPage($pageNumber);
}
