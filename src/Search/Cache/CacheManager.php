<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Search\Cache;

use Broadway\Domain\DomainMessage;
use Broadway\EventHandling\EventListenerInterface;
use Predis\ClientInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

/**
 * Cache manager allows you to flag the cache as outdated and warm up the cache
 * at a later point in time.
 */
class CacheManager implements EventListenerInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @var CacheHandlerInterface
     */
    private $cacheHandler;

    /**
     * @var ClientInterface
     */
    private $redis;

    /**
     * @var string
     */
    private $redisKey;

    /**
     * @param CacheHandlerInterface $cacheHandler
     * @param ClientInterface $redis
     * @param string $redisKey
     */
    public function __construct(
        CacheHandlerInterface $cacheHandler,
        ClientInterface $redis,
        $redisKey = 'search-cache-outdated'
    ) {
        $this->cacheHandler = $cacheHandler;
        $this->redis = $redis;
        $this->redisKey = $redisKey;
    }

    public function flagCacheAsOutdated()
    {
        $this->redis->set($this->redisKey, 1);
    }

    private function flagCacheAsFresh()
    {
        $this->redis->set($this->redisKey, 0);
    }

    public function clearCache()
    {
        // Mark empty cache as outdated so the next warm-up doesn't think
        // the cache is filled and up-to-date.
        $this->flagCacheAsOutdated();
        $this->cacheHandler->clearCache();
    }

    /**
     * @return void
     */
    public function warmUpCacheIfNeeded()
    {
        $flaggedAsOutdated = $this->isCacheFlaggedAsOutdated();

        if ($flaggedAsOutdated) {
            if ($this->logger) {
                $this->logger->info('Cache marked as outdated, warming up again');
            }

            $this->flagCacheAsFresh();
            $this->cacheHandler->warmUpCache();
        } else {
            if ($this->logger) {
                $this->logger->info('Cache was not marked as outdated, skipping');
            }
        }
    }

    /**
     * @return boolean
     */
    private function isCacheFlaggedAsOutdated()
    {
        $flag = $this->redis->get($this->redisKey);
        return $flag == 1;
    }

    /**
     * @param DomainMessage $domainMessage
     */
    public function handle(DomainMessage $domainMessage)
    {
        $event = $domainMessage->getPayload();

        $offerNamespaces = [
            'CultuurNet\UDB3\Event',
            'CultuurNet\UDB3\Offer',
            'CultuurNet\UDB3\Place',
        ];

        foreach ($offerNamespaces as $offerNamespace) {
            if (strpos(get_class($event), $offerNamespace) === 0) {
                $this->flagCacheAsOutdated();
                break;
            }
        }
    }
}
