<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Search;

use Broadway\Domain\DomainMessage;
use Broadway\EventHandling\EventListenerInterface;
use Doctrine\Common\Cache\Cache;

class CachedDefaultSearchService implements SearchServiceInterface, EventListenerInterface
{
    /**
     * @var \CultuurNet\UDB3\Search\SearchServiceInterface
     */
    protected $search;

    /**
     * @var Cache
     */
    protected $cache;

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
            $cacheResult = $this->cache->fetch('default-search');
            if ($cacheResult) {
                return unserialize($cacheResult);
            } else {
                $result = $this->search->search($query, $limit, $start, $sort);
                $this->cache->save('default-search', serialize($result));

                return $result;
            }
        } else {
            return $this->search->search($query, $limit, $start, $sort);
        }
    }

    public function warmUpCache()
    {
        $result = $this->search->search('*.*', 30, 0, 'lastupdated desc');
        $this->cache->save('default-search', serialize($result));
    }
}
