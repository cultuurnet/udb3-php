<?php

namespace CultuurNet\UDB3\Search;

use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\Cache;

class CachedDefaultSearchServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SearchServiceInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $searchServiceInterface;

    /**
     * @var Cache|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $cache;

    /**
     * @var CachedDefaultSearchService
     */
    protected $cachedDefaultSearchService;

    public function SetUp()
    {
        $this->searchServiceInterface = $this->getMock(
            SearchServiceInterface::class
        );

        $this->cache = $this->getMock(
            Cache::class
        );

        $this->cachedDefaultSearchService = new CachedDefaultSearchService($this->searchServiceInterface, $this->cache);
    }

    /**
     * @test
     */
    public function it_caches_default_searches()
    {
        // Do a default search.
        $searchResultJson = file_get_contents(dirname(__FILE__) . '/samples/search_result.json');
        $cacheKey = 'default-search:30:0:0';

        $arrayCache = new ArrayCache();
        $CachedDefaultSearchService = new CachedDefaultSearchService($this->searchServiceInterface, $arrayCache);

        $this->searchServiceInterface->expects($this->once())
            ->method('search')
            ->with(
                '*.*',
                30,
                0
            )
            ->will($this->returnValue($searchResultJson));

        $CachedDefaultSearchService->search(
            '*.*',
            30,
            0
        );

        $this->assertEquals($searchResultJson, $arrayCache->fetch($cacheKey));
    }

    /**
     * @test
     */
    function it_does_not_cache_non_default_searches()
    {
        $this->cache->expects($this->never())
            ->method('save');

        // Do some non default search.
        $this->cachedDefaultSearchService->search(
            'title:organic',
            30,
            0
        );
        $this->cachedDefaultSearchService->search(
            '*:organic',
            30,
            0
        );
        $this->cachedDefaultSearchService->search(
            'title:*',
            30,
            0
        );
    }

    /**
     * @test
     */
    public function it_rebuilds_the_cache_when_an_event_is_altered()
    {

    }

    /**
     * @test
     */
    public function it_has_an_empty_cache_on_first_search()
    {

    }

    /**
     * @test
     */
    public function it_has_a_cache_on_following_searches()
    {

    }
}
