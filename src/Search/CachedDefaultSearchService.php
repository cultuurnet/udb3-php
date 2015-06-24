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
            $this->cache->delete('default-search');
            $result = $this->search->search('*.*', 30, 0, 'lastupdated desc');
            $this->cache->save('default-search', serialize($result));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function search($query, $limit = 30, $start = 0, $sort = null, $unavailable = true, $past = true)
    {
        if ($query == '*.*' &&
            $limit == 30 &&
            $start == 0 &&
            $sort == 'lastupdated desc' &&
            $unavailable &&
            $past
        ) {
            $cacheResult = $this->cache->fetch('default-search');
            if ($cacheResult) {
                return unserialize($cacheResult);
            } else {
                $result = $this->search->search($query, $limit, $start, $sort, $unavailable, $past);
                $this->cache->save('default-search', serialize($result));

                return $result;
            }
        } else {
            return $this->search->search($query, $limit, $start, $sort, $unavailable, $past);
        }
    }
}
