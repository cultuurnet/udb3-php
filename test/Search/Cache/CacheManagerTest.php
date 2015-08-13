<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Search\Cache;

use Broadway\Domain\DomainMessage;
use Broadway\Domain\Metadata;
use CultuurNet\UDB3\Event\Events\EventWasLabelled;
use CultuurNet\UDB3\Label;
use Predis\ClientInterface;

class CacheManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ClientInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $redis;

    /**
     * @var string
     */
    protected $redisKey;

    /**
     * @var WarmUpInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $search;

    /**
     * @var CacheManager
     */
    protected $cacheManager;

    public function setUp()
    {
        $this->search = $this->getMock(WarmUpInterface::class);
        $this->redis = $this->getMock(ClientInterface::class, ['set', 'get']);
        $this->redisKey = 'cache-outdated';

        $this->cacheManager = new CacheManager(
            $this->search,
            $this->redis,
            $this->redisKey
        );
    }

    /**
     * @test
     */
    public function it_can_flag_the_cache_as_outdated()
    {
        $this->redis->expects($this->once())
            ->method('set')
            ->with($this->redisKey, 1);

        $this->cacheManager->flagCacheAsOutdated();
    }

    /**
     * @test
     */
    public function it_warms_the_cache_when_it_was_flagged_as_outdated()
    {
        $this->redis->expects($this->once())
            ->method('get')
            ->with($this->redisKey)
            ->willReturn(1);

        $this->redis->expects($this->once())
            ->method('set')
            ->with($this->redisKey, 0);

        $this->search->expects($this->once())
            ->method('warmUpCache');

        $this->cacheManager->warmUpCacheIfNeeded();
    }

    /**
     * @test
     */
    public function it_does_not_warm_the_cache_when_it_was_not_flagged_as_outdated()
    {
        $this->redis->expects($this->once())
            ->method('get')
            ->with($this->redisKey)
            ->willReturn(0);

        $this->redis->expects($this->never())
            ->method('set');

        $this->search->expects($this->never())
            ->method('warmUpCache');

        $this->cacheManager->warmUpCacheIfNeeded();
    }

    /**
     * @test
     */
    public function it_flags_the_cache_as_outdated_on_event_related_messages()
    {
        $this->redis->expects($this->once())
            ->method('set')
            ->with($this->redisKey, 1);

        $this->search->expects($this->never())
            ->method('warmUpCache');

        $payload = new EventWasLabelled(
            'xyz-123',
            new Label('test')
        );

        $message = DomainMessage::recordNow(
            'foo',
            1,
            new Metadata(),
            $payload
        );

        $this->cacheManager->handle($message);
    }
}
