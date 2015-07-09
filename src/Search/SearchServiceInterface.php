<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Search;

/**
 * Interface for a service responsible for search-related tasks.
 */
interface SearchServiceInterface
{
    /**
     * Find UDB3 data based on an arbitrary query.
     *
     * @param string $query
     *   An arbitrary query.
     * @param int $limit
     *   How many items to retrieve.
     * @param int $start
     *   Offset to start from.
     * @param string $sort
     *   Sort by field.
     *
     * @return Results
     */
    public function search($query, $limit = 30, $start = 0, $sort = null);
}
