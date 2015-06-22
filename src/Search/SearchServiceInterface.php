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
     * @param string sort
     *   The order in which items will be returned
     * @param boolean $unavailable
     *   Shows items that have an embargo date that has not yet passed.
     * @param boolean $past
     *   Show items that happend in the past.
     *
     * @return array|\JsonSerializable
     *  A JSON-LD array or JSON serializable object.
     */
    public function search($query, $limit = 30, $start = 0, $sort = null, $unavailable = true, $past = true);
}
