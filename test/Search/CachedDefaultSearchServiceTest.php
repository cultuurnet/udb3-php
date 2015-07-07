<?php

namespace CultuurNet\UDB3\Search;

use Broadway\Domain\DomainEventStream;
use Broadway\Domain\DomainMessage;
use Broadway\Domain\Metadata;
use Broadway\EventHandling\SimpleEventBus;
use Broadway\UuidGenerator\Rfc4122\Version4Generator;
use CultuurNet\UDB3\Event\Events\EventWasLabelled;
use CultuurNet\UDB3\Label;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\Cache;

class CachedDefaultSearchServiceTest extends \PHPUnit_Framework_TestCase
{
    protected $cacheKey = 'default-search';
    protected $searchResultJson;
    protected $searchParams;

    /**
     * @var SearchServiceInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $searchService;

    /**
     * @var Cache|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $cache;

    /**
     * @var ArrayCache
     */
    protected $arrayCache;

    /**
     * @var CachedDefaultSearchService
     */
    protected $arrayCachedSearchService;

    /**
     * @var CachedDefaultSearchService
     */
    protected $mockCachedSearchService;

    /**
     * @var SimpleEventBus
     */
    protected $eventBus;

    public function SetUp()
    {
        $this->searchResultJson = file_get_contents(dirname(__FILE__) . '/samples/search_result.json');
        $this->searchParams = array(
            'query' => '*.*',
            'limit' => 30,
            'start' => 0,
            'sort' => 'lastupdated desc'
        );

        $this->searchService = $this->getMock(
            SearchServiceInterface::class
        );

        $this->cache = $this->getMock(
            Cache::class
        );

        $this->arrayCache = new ArrayCache();

        $this->arrayCachedSearchService = new CachedDefaultSearchService($this->searchService, $this->arrayCache);

        $this->mockCachedSearchService = new CachedDefaultSearchService($this->searchService, $this->cache);

        $this->eventBus = new SimpleEventBus();
        $this->eventBus->subscribe($this->mockCachedSearchService);
    }

    /**
     * @test
     */
    public function it_caches_default_searches()
    {
        $this->searchService->expects($this->once())
            ->method('search')
            ->with(
                $this->searchParams['query'],
                $this->searchParams['limit'],
                $this->searchParams['start'],
                $this->searchParams['sort']
            )
            ->will($this->returnValue($this->searchResultJson));

        $this->arrayCachedSearchService->search(
            $this->searchParams['query'],
            $this->searchParams['limit'],
            $this->searchParams['start'],
            $this->searchParams['sort']
        );

        $this->assertEquals(serialize($this->searchResultJson), $this->arrayCache->fetch($this->cacheKey));
    }

    /**
     * @test
     */
    public function it_does_not_cache_non_default_searches()
    {
        $this->cache->expects($this->never())
            ->method('save');

        // Do some non default search.
        $this->mockCachedSearchService->search(
            'title:organic',
            $this->searchParams['limit'],
            $this->searchParams['start'],
            $this->searchParams['sort']
        );
        $this->mockCachedSearchService->search(
            $this->searchParams['query'],
            60,
            $this->searchParams['start'],
            $this->searchParams['sort']
        );
        $this->mockCachedSearchService->search(
            $this->searchParams['query'],
            $this->searchParams['limit'],
            30,
            $this->searchParams['sort']
        );
        $this->mockCachedSearchService->search(
            $this->searchParams['query'],
            $this->searchParams['limit'],
            $this->searchParams['start'],
            'lastupdated asc'
        );
    }

    /**
     * @test
     */
    public function it_rebuilds_the_cache_when_an_event_is_altered()
    {
        $event = new EventWasLabelled('02139bf4-6c09-4d05-9368-9b72d8b2307f', new Label('label'));
        $this->cache->expects($this->once())
            ->method('save');

        $generator = new Version4Generator();
        $events[] = DomainMessage::recordNow(
            $generator->generate(),
            1,
            new Metadata(),
            $event
        );

        $this->searchService->expects($this->once())
            ->method('search')
            ->with(
                $this->searchParams['query'],
                $this->searchParams['limit'],
                $this->searchParams['start'],
                $this->searchParams['sort']
            )
            ->will($this->returnValue($this->searchResultJson));

        $this->eventBus->publish(
            new DomainEventStream($events)
        );
    }

    /**
     * @test
     */
    public function it_has_an_empty_cache_on_first_search()
    {
        // The cache should be empty before the search.
        $this->assertEquals(null, $this->arrayCache->fetch('default-search'));

        $this->searchService->expects($this->once())
            ->method('search')
            ->with(
                $this->searchParams['query'],
                $this->searchParams['limit'],
                $this->searchParams['start'],
                $this->searchParams['sort']
            )
            ->will($this->returnValue($this->searchResultJson));

        $this->arrayCachedSearchService->search(
            $this->searchParams['query'],
            $this->searchParams['limit'],
            $this->searchParams['start'],
            $this->searchParams['sort']
        );

        // The cache should be filled after the search.
        $this->assertEquals(serialize($this->searchResultJson), $this->arrayCache->fetch($this->cacheKey));
    }

    /**
     * @test
     */
    public function it_has_a_cache_on_following_searches()
    {
        $this->searchService->expects($this->any())
            ->method('search')
            ->with(
                $this->searchParams['query'],
                $this->searchParams['limit'],
                $this->searchParams['start'],
                $this->searchParams['sort']
            )
            ->will($this->returnValue($this->searchResultJson));

        $this->arrayCachedSearchService->search(
            $this->searchParams['query'],
            $this->searchParams['limit'],
            $this->searchParams['start'],
            $this->searchParams['sort']
        );

        // The cache should be filled after the search.
        $this->assertEquals(serialize($this->searchResultJson), $this->arrayCache->fetch($this->cacheKey));

        $this->arrayCachedSearchService->search(
            $this->searchParams['query'],
            $this->searchParams['limit'],
            $this->searchParams['start'],
            $this->searchParams['sort']
        );

        // The cache should be filled after the search.
        $this->assertEquals(serialize($this->searchResultJson), $this->arrayCache->fetch($this->cacheKey));
    }
}
