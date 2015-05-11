<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Search;

use Doctrine\Common\Cache\Cache;

class CachedDefaultSearchService implements SearchServiceInterface
{
    /**
     * @var Cache
     */
    protected $cache;

    /**
     * @var \\CultuurNet\UDB3\Search\SearchServiceInterface
     */
    protected $search;

    /**
     * @param \CultuurNet\UDB3\Search\SearchServiceInterface $search
     * @param \Doctrine\Common\Cache\Cache $cache
     */
    public function __construct(SearchServiceInterface $search, Cache $cache) {
        $this->cache = $cache;
        $this->search = $search;
    }

    /**
     * Find UDB3 data based on an arbitrary query.
     *
     * @param string $query
     *   An arbitrary query.
     * @param int $limit
     *   How many items to retrieve.
     * @param int $start
     *   Offset to start from.
     *
     * @return array|\JsonSerializable
     *  A JSON-LD array or JSON serializable object.
     */
    public function search($query, $limit = 30, $start = 0, $sort = NULL) {
        if ($query == '*.*') {
            $cacheResult = $this->cache->fetch('default-search:' . $limit . ':' . $start . ':' . (int) $sort);
            if ($cacheResult) {
                return $cacheResult;
            }
            else {
                $result = $this->search->search($query, $limit, $start, $sort);
                $this->cache->save('default-search:' . $limit . ':' . $start . ':' . (int) $sort, $result);

                return $result;
            }
        }
        else {
            return $this->search->search($query, $limit, $start, $sort);
        }
    }

}
