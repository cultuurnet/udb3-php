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
use Psr\Log\LogLevel;

/**
 * Cache manager allows you to flag the cache as outdated and warm up the cache
 * at a later point in time.
 */
class CacheManager implements EventListenerInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @var WarmUpInterface
     */
    private $search;

    /**
     * @var ClientInterface
     */
    private $redis;

    /**
     * @var string
     */
    private $redisKey;

    /**
     * @param WarmUpInterface $search
     * @param ClientInterface $redis
     * @param string $redisKey
     */
    public function __construct(
        WarmUpInterface $search,
        ClientInterface $redis,
        $redisKey = 'search-cache-outdated'
    ) {
        $this->search = $search;
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

    /**
     * @return void
     */
    public function warmUpCacheIfNeeded()
    {
        $flaggedAsOutdated = $this->isCacheFlaggedAsOutdated();

        if ($flaggedAsOutdated) {
            if ($this->logger) {
                $this->logger->info('cache marked as outdated, warming up again');
            }

            $this->flagCacheAsFresh();
            $this->search->warmUpCache();
        } else {
            if ($this->logger) {
                $this->logger->debug('cache was not marked as outdated, skipping');
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

        if (strpos(get_class($event), 'CultuurNet\UDB3\Event') === 0) {
            $this->flagCacheAsOutdated();
        }
    }
}
