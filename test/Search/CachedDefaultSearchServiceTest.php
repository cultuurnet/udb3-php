<?php

namespace CultuurNet\UDB3\Search;

use Broadway\Domain\DomainEventStream;
use Broadway\Domain\DomainMessage;
use Broadway\Domain\Metadata;
use Broadway\EventHandling\SimpleEventBus;
use Broadway\UuidGenerator\Rfc4122\Version4Generator;
use CultuurNet\UDB3\Event\Events\LabelAdded;
use CultuurNet\UDB3\Label;
use CultuurNet\UDB3\Offer\IriOfferIdentifier;
use CultuurNet\UDB3\Offer\OfferIdentifierCollection;
use CultuurNet\UDB3\Offer\OfferType;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\Cache;
use ValueObjects\Number\Integer;

class CachedDefaultSearchServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Results
     */
    protected $searchResult;

    /**
     * @var array
     */
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
        $this->searchResult = new Results(
            OfferIdentifierCollection::fromArray(
                [
                    new IriOfferIdentifier(
                        "http://culudb-silex.dev:8080/event/cffa02de-a164-4649-8505-43d57cf0b6c3",
                        "cffa02de-a164-4649-8505-43d57cf0b6c3",
                        OfferType::EVENT()
                    ),
                    new IriOfferIdentifier(
                        "http://culudb-silex.dev:8080/event/9bb13516-b88d-41f7-83a2-5022bc3d159b",
                        "9bb13516-b88d-41f7-83a2-5022bc3d159b",
                        OfferType::EVENT()
                    ),
                    new IriOfferIdentifier(
                        "http://culudb-silex.dev:8080/event/cd96eadc-ec48-4c03-bc3a-6eb5f991a87c",
                        "cd96eadc-ec48-4c03-bc3a-6eb5f991a87c",
                        OfferType::EVENT()
                    ),
                    new IriOfferIdentifier(
                        "http://culudb-silex.dev:8080/event/d239f3b9-08a3-45f0-a138-3bc994fac8f2",
                        "d239f3b9-08a3-45f0-a138-3bc994fac8f2",
                        OfferType::EVENT()
                    ),
                    new IriOfferIdentifier(
                        "http://culudb-silex.dev:8080/event/d989d2c5-c042-46f9-8884-f1df87c31b82",
                        "d989d2c5-c042-46f9-8884-f1df87c31b82",
                        OfferType::EVENT()
                    ),
                    new IriOfferIdentifier(
                        "http://culudb-silex.dev:8080/event/68e7139a-39a0-4eaa-b44d-9310611d11e5",
                        "68e7139a-39a0-4eaa-b44d-9310611d11e5",
                        OfferType::EVENT()
                    ),
                    new IriOfferIdentifier(
                        "http://culudb-silex.dev:8080/event/8383ed8f-ca4d-4697-8998-5c5dcb013b74",
                        "8383ed8f-ca4d-4697-8998-5c5dcb013b74",
                        OfferType::EVENT()
                    ),
                    new IriOfferIdentifier(
                        "http://culudb-silex.dev:8080/event/b73f2f0f-b07f-4afa-8600-ddd199e95d2f",
                        "b73f2f0f-b07f-4afa-8600-ddd199e95d2f",
                        OfferType::EVENT()
                    ),
                    new IriOfferIdentifier(
                        "http://culudb-silex.dev:8080/event/0b5472d5-aa01-413c-8f1c-e60fd3f64e41",
                        "0b5472d5-aa01-413c-8f1c-e60fd3f64e41",
                        OfferType::EVENT()
                    ),
                    new IriOfferIdentifier(
                        "http://culudb-silex.dev:8080/event/2f72b05e-c020-4f5d-8c46-5e2a190a3d23",
                        "2f72b05e-c020-4f5d-8c46-5e2a190a3d23",
                        OfferType::EVENT()
                    ),
                    new IriOfferIdentifier(
                        "http://culudb-silex.dev:8080/event/23fb8508-e1fb-4c1d-a089-aed726957f2b",
                        "23fb8508-e1fb-4c1d-a089-aed726957f2b",
                        OfferType::EVENT()
                    ),
                    new IriOfferIdentifier(
                        "http://culudb-silex.dev:8080/event/1eaf1faa-c90a-4110-a4ef-c493ffc46897",
                        "1eaf1faa-c90a-4110-a4ef-c493ffc46897",
                        OfferType::EVENT()
                    ),
                    new IriOfferIdentifier(
                        "http://culudb-silex.dev:8080/event/57a2394d-1e2f-460a-9d71-b7d466e9d121",
                        "57a2394d-1e2f-460a-9d71-b7d466e9d121",
                        OfferType::EVENT()
                    ),
                    new IriOfferIdentifier(
                        "http://culudb-silex.dev:8080/event/197c3464-766c-4b4a-ac21-58ad9d52b63d",
                        "197c3464-766c-4b4a-ac21-58ad9d52b63d",
                        OfferType::EVENT()
                    ),
                    new IriOfferIdentifier(
                        "http://culudb-silex.dev:8080/event/018eb9eb-9e84-4daf-b33e-eb68db1d51c1",
                        "018eb9eb-9e84-4daf-b33e-eb68db1d51c1",
                        OfferType::EVENT()
                    ),
                    new IriOfferIdentifier(
                        "http://culudb-silex.dev:8080/event/15564d48-c9df-42cd-8184-8df3c56f731b",
                        "15564d48-c9df-42cd-8184-8df3c56f731b",
                        OfferType::EVENT()
                    ),
                    new IriOfferIdentifier(
                        "http://culudb-silex.dev:8080/event/53a3c63a-68f9-4c6a-aa72-7ea857e40851",
                        "53a3c63a-68f9-4c6a-aa72-7ea857e40851",
                        OfferType::EVENT()
                    ),
                    new IriOfferIdentifier(
                        "http://culudb-silex.dev:8080/event/83d20649-c067-4287-b03a-e34f8492ce39",
                        "83d20649-c067-4287-b03a-e34f8492ce39",
                        OfferType::EVENT()
                    ),
                    new IriOfferIdentifier(
                        "http://culudb-silex.dev:8080/event/dd98e247-5bb7-490f-a296-9c4fce37d260",
                        "dd98e247-5bb7-490f-a296-9c4fce37d260",
                        OfferType::EVENT()
                    ),
                    new IriOfferIdentifier(
                        "http://culudb-silex.dev:8080/event/aeee64c8-87ba-414c-aeae-e259f65643d3",
                        "aeee64c8-87ba-414c-aeae-e259f65643d3",
                        OfferType::EVENT()
                    ),
                    new IriOfferIdentifier(
                        "http://culudb-silex.dev:8080/event/57008044-5ea1-412e-b303-8984bdd0317d",
                        "57008044-5ea1-412e-b303-8984bdd0317d",
                        OfferType::EVENT()
                    ),
                    new IriOfferIdentifier(
                        "http://culudb-silex.dev:8080/event/4c94d007-4355-4d7d-854a-fcda2ebbef6e",
                        "4c94d007-4355-4d7d-854a-fcda2ebbef6e",
                        OfferType::EVENT()
                    ),
                    new IriOfferIdentifier(
                        "http://culudb-silex.dev:8080/event/cb5e1ade-e40c-4ef8-9b6e-6ebc2b3c650c",
                        "cb5e1ade-e40c-4ef8-9b6e-6ebc2b3c650c",
                        OfferType::EVENT()
                    ),
                    new IriOfferIdentifier(
                        "http://culudb-silex.dev:8080/event/7179343a-6fd8-4bee-b41d-34efc5271111",
                        "7179343a-6fd8-4bee-b41d-34efc5271111",
                        OfferType::EVENT()
                    ),
                    new IriOfferIdentifier(
                        "http://culudb-silex.dev:8080/event/9df36618-f279-4a4c-90bb-34ecc59a4c81",
                        "9df36618-f279-4a4c-90bb-34ecc59a4c81",
                        OfferType::EVENT()
                    ),
                    new IriOfferIdentifier(
                        "http://culudb-silex.dev:8080/event/331964dd-adb1-48e2-baa3-8dbdbf3aff70",
                        "331964dd-adb1-48e2-baa3-8dbdbf3aff70",
                        OfferType::EVENT()
                    ),
                    new IriOfferIdentifier(
                        "http://culudb-silex.dev:8080/event/bd2e1fff-f747-44f7-86e8-d224cf986026",
                        "bd2e1fff-f747-44f7-86e8-d224cf986026",
                        OfferType::EVENT()
                    ),
                    new IriOfferIdentifier(
                        "http://culudb-silex.dev:8080/event/ec1c5d63-f22a-44a9-b399-91c53a335348",
                        "ec1c5d63-f22a-44a9-b399-91c53a335348",
                        OfferType::EVENT()
                    ),
                    new IriOfferIdentifier(
                        "http://culudb-silex.dev:8080/event/bc4653c8-b39e-434c-80ab-4da138f41f68",
                        "bc4653c8-b39e-434c-80ab-4da138f41f68",
                        OfferType::EVENT()
                    ),
                    new IriOfferIdentifier(
                        "http://culudb-silex.dev:8080/event/5513151b-3c7d-40b4-9f05-36e7d7a6fef4",
                        "5513151b-3c7d-40b4-9f05-36e7d7a6fef4",
                        OfferType::EVENT()
                    )
                ]
            ),
            new Integer(878)
        );

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

        $this->arrayCachedSearchService = new CachedDefaultSearchService(
            $this->searchService,
            $this->arrayCache
        );

        $this->mockCachedSearchService = new CachedDefaultSearchService(
            $this->searchService,
            $this->cache
        );

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
            ->will($this->returnValue($this->searchResult));

        $result = $this->arrayCachedSearchService->search(
            $this->searchParams['query'],
            $this->searchParams['limit'],
            $this->searchParams['start'],
            $this->searchParams['sort']
        );

        $this->assertEquals($this->searchResult, $result);

        $this->assertEquals(
            serialize($this->searchResult),
            $this->arrayCache->fetch(CachedDefaultSearchService::CACHE_KEY)
        );
    }

    /**
     * @test
     */
    public function it_detects_a_cache_filled_with_wrong_sort_of_data()
    {
        // Pollute the cache with an empty array instead of a Result object.
        $this->arrayCache->save(
            CachedDefaultSearchService::CACHE_KEY,
            serialize([])
        );

        $this->searchService->expects($this->once())
            ->method('search')
            ->with(
                $this->searchParams['query'],
                $this->searchParams['limit'],
                $this->searchParams['start'],
                $this->searchParams['sort']
            )
            ->will($this->returnValue($this->searchResult));

        $result = $this->arrayCachedSearchService->search(
            $this->searchParams['query'],
            $this->searchParams['limit'],
            $this->searchParams['start'],
            $this->searchParams['sort']
        );

        $this->assertEquals($this->searchResult, $result);

        $this->assertEquals(
            serialize($this->searchResult),
            $this->arrayCache->fetch(CachedDefaultSearchService::CACHE_KEY)
        );
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
        $event = new LabelAdded(
            '02139bf4-6c09-4d05-9368-9b72d8b2307f',
            new Label('label')
        );

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
            ->will($this->returnValue($this->searchResult));

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
        $this->assertEquals(null, $this->arrayCache->fetch(CachedDefaultSearchService::CACHE_KEY));

        $this->searchService->expects($this->once())
            ->method('search')
            ->with(
                $this->searchParams['query'],
                $this->searchParams['limit'],
                $this->searchParams['start'],
                $this->searchParams['sort']
            )
            ->will($this->returnValue($this->searchResult));

        $this->arrayCachedSearchService->search(
            $this->searchParams['query'],
            $this->searchParams['limit'],
            $this->searchParams['start'],
            $this->searchParams['sort']
        );

        // The cache should be filled after the search.
        $this->assertEquals(
            serialize($this->searchResult),
            $this->arrayCache->fetch(CachedDefaultSearchService::CACHE_KEY)
        );
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
            ->will($this->returnValue($this->searchResult));

        $this->arrayCachedSearchService->search(
            $this->searchParams['query'],
            $this->searchParams['limit'],
            $this->searchParams['start'],
            $this->searchParams['sort']
        );

        // The cache should be filled after the search.
        $this->assertEquals(
            serialize($this->searchResult),
            $this->arrayCache->fetch(CachedDefaultSearchService::CACHE_KEY)
        );

        $this->arrayCachedSearchService->search(
            $this->searchParams['query'],
            $this->searchParams['limit'],
            $this->searchParams['start'],
            $this->searchParams['sort']
        );

        // The cache should be filled after the search.
        $this->assertEquals(
            serialize($this->searchResult),
            $this->arrayCache->fetch(CachedDefaultSearchService::CACHE_KEY)
        );
    }

    /**
     * @test
     */
    public function it_can_clear_the_cache()
    {
        $this->cache->expects($this->once())
            ->method('delete')
            ->with(CachedDefaultSearchService::CACHE_KEY);

        $this->mockCachedSearchService->clearCache();
    }
}
