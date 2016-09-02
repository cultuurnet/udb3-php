<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Search;

use Broadway\Domain\DomainMessage;
use Broadway\EventHandling\EventListenerInterface;
use CultuurNet\UDB3\Search\Cache\CacheHandlerInterface;
use Doctrine\Common\Cache\Cache;

class CachedDefaultSearchService implements SearchServiceInterface, EventListenerInterface, CacheHandlerInterface
{
    /**
     * @var \CultuurNet\UDB3\Search\SearchServiceInterface
     */
    protected $search;

    /**
     * @var Cache
     */
    protected $cache;

    const CACHE_KEY = 'default-search';

    /**
     * @param \CultuurNet\UDB3\Search\SearchServiceInterface $search
     * @param \Doctrine\Common\Cache\Cache $cache
     */
    public function __construct(SearchServiceInterface $search, Cache $cache)
    {
        $this->search = $search;
        $this->cache = $cache;
    }

    /**
     * @param DomainMessage $domainMessage
     */
    public function handle(DomainMessage $domainMessage)
    {
        $event = $domainMessage->getPayload();

        if (strpos(get_class($event), 'CultuurNet\UDB3\Event') === 0) {
            $this->warmUpCache();
        }
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
    public function search($query, $limit = 30, $start = 0, $sort = null)
    {
        if ($query == '*.*' && $limit == 30 && $start == 0 && $sort == 'lastupdated desc') {
            $result = $this->fetchFromCache();

            if (null === $result) {
                $result = $this->search->search($query, $limit, $start, $sort);
                $this->saveToCache($result);
            }

            return $result;
        } else {
            return $this->search->search($query, $limit, $start, $sort);
        }
    }

    /**
     * @return Results|null
     */
    private function fetchFromCache()
    {
        $cacheResult = $this->cache->fetch(self::CACHE_KEY);
        if ($cacheResult) {
            $result = unserialize($cacheResult);

            if (is_object($result) && $result instanceof Results) {
                return $result;
            }
        }
    }

    /**
     * @param Results $result
     */
    private function saveToCache(Results $result)
    {
        $this->cache->save(self::CACHE_KEY, serialize($result));
    }

    /**
     * {@inheritdoc}
     */
    public function warmUpCache()
    {
        $result = $this->search->search('*.*', 30, 0, 'lastupdated desc');
        $this->saveToCache($result);
    }

    /**
     * {@inheritdoc}
     */
    public function clearCache()
    {
        $this->cache->delete(self::CACHE_KEY);
    }
}
