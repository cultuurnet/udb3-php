<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Search\Cache;

use Broadway\Domain\DomainMessage;
use Broadway\Domain\Metadata;
use CultuurNet\UDB3\Event\Events\LabelAdded as EventLabelAdded;
use CultuurNet\UDB3\Offer\Commands\AddLabelToQuery;
use CultuurNet\UDB3\Place\Events\LabelAdded as PlaceLabelAdded;
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
     * @var CacheHandlerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $cacheHandler;

    /**
     * @var CacheManager
     */
    protected $cacheManager;

    public function setUp()
    {
        $this->cacheHandler = $this->getMock(CacheHandlerInterface::class);

        // We need to explicitly tell PHPUnit to mock get() & set() because
        // they are magic methods implemented using __call(). But because
        // PHPUnit would then only implement those two methods, we also need
        // to define the other methods of the interface because otherwise the
        // mock is not an actual implementation of the interface.
        $this->redis = $this->getMock(
            ClientInterface::class,
            [
                'get',
                'set',
                'getProfile',
                'getOptions',
                'connect',
                'disconnect',
                'getConnection',
                'createCommand',
                'executeCommand',
                '__call',
            ]
        );

        $this->redisKey = 'cache-outdated';

        $this->cacheManager = new CacheManager(
            $this->cacheHandler,
            $this->redis,
            $this->redisKey
        );
    }

    /**
     * @test
     */
    public function it_can_flag_the_cache_as_outdated()
    {
        $this->cacheShouldBeFlaggedAsOutdated();
        $this->cacheManager->flagCacheAsOutdated();
    }

    /**
     * @test
     */
    public function it_warms_the_cache_when_it_was_flagged_as_outdated()
    {
        $this->cacheIsFlaggedAsOutdated();

        $this->cacheShouldBeFlaggedAsFresh();

        $this->cacheHandler->expects($this->once())
            ->method('warmUpCache');

        $this->cacheManager->warmUpCacheIfNeeded();
    }

    /**
     * @test
     */
    public function it_does_not_warm_the_cache_when_it_was_not_flagged_as_outdated()
    {
        $this->cacheIsFlaggedAsFresh();

        $this->redis->expects($this->never())
            ->method('set');

        $this->cacheHandler->expects($this->never())
            ->method('warmUpCache');

        $this->cacheManager->warmUpCacheIfNeeded();
    }

    /**
     * @test
     * @dataProvider offerRelatedMessageProvider()
     *
     * @param DomainMessage $message
     */
    public function it_flags_the_cache_as_outdated_on_offer_related_messages(DomainMessage $message)
    {
        $this->cacheShouldBeFlaggedAsOutdated();

        $this->cacheHandler->expects($this->never())
            ->method('warmUpCache');

        $this->cacheManager->handle($message);
    }

    /**
     * @return array
     */
    public function offerRelatedMessageProvider()
    {
        return [
            [
                DomainMessage::recordNow(
                    'foo',
                    1,
                    new Metadata(),
                    new EventLabelAdded(
                        'xyz-123',
                        new Label('test-1')
                    )
                ),
            ],
            [
                DomainMessage::recordNow(
                    'bar',
                    1,
                    new Metadata(),
                    new PlaceLabelAdded(
                        'abc-456',
                        new Label('test-2')
                    )
                ),
            ],
            [
                DomainMessage::recordNow(
                    'baz',
                    1,
                    new Metadata(),
                    new AddLabelToQuery(
                        'city:leuven',
                        new Label('test-3')
                    )
                )
            ],
        ];
    }

    /**
     * @test
     */
    public function it_can_clear_the_cache()
    {
        $this->cacheShouldBeFlaggedAsOutdated();

        $this->cacheHandler->expects($this->once())
            ->method('clearCache');

        $this->cacheManager->clearCache();
    }

    private function cacheShouldBeFlaggedAsOutdated()
    {
        $this->redis->expects($this->once())
            ->method('set')
            ->with($this->redisKey, 1);
    }

    private function cacheShouldBeFlaggedAsFresh()
    {
        $this->redis->expects($this->once())
            ->method('set')
            ->with($this->redisKey, 0);
    }

    private function cacheIsFlaggedAsOutdated()
    {
        $this->redis->expects($this->once())
            ->method('get')
            ->with($this->redisKey)
            ->willReturn(1);
    }

    private function cacheIsFlaggedAsFresh()
    {
        $this->redis->expects($this->once())
            ->method('get')
            ->with($this->redisKey)
            ->willReturn(0);
    }
}
