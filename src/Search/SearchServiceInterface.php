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
     * @param ? $sort
     *   Sort by field
     * @param array $conditions
     *   Extra conditions to extend the query
     *
     * @return array|\JsonSerializable
     *  A JSON-LD array or JSON serializable object.
     */
    public function search($query, $limit = 30, $start = 0, $sort = null, $conditions = array());
}
