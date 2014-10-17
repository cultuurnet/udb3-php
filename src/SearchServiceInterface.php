<?php
/**
 * @file
 */

namespace CultuurNet\UDB3;


interface SearchServiceInterface
{
    /**
     * @param string $q
     * @return array
     *  A Event-LD array.
     */
    public function search($q, $limit = 30, $start = 0);
} 
