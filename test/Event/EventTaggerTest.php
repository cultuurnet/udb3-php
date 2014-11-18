<?php

namespace CultuurNet\UDB3\Event;

use Broadway\CommandHandling\Testing\CommandHandlerScenarioTestCase;
use Broadway\EventStore\EventStoreInterface;
use Broadway\EventHandling\EventBusInterface;
use CultuurNet\UDB3\Keyword;
use CultuurNet\UDB3\Language;
use CultuurNet\UDB3\Search\SearchServiceInterface;

class EventTaggerTest extends CommandHandlerScenarioTestCase
{
    /**
     * @var SearchServiceInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $search;

    public function setUp()
    {
        $this->search = $this->getMock(
            'CultuurNet\\UDB3\\Search\\SearchServiceInterface'
        );

        parent::setUp();
    }

    protected function createCommandHandler(
        EventStoreInterface $eventStore,
        EventBusInterface $eventBus
    ) {
        $repository = new EventRepository(
            $eventStore,
            $eventBus
        );

        return new EventCommandHandler($repository, $this->search);
    }

    /**
     * @test
     */
    public function it_can_tag_a_list_of_events_with_a_keyword()
    {
        $ids = ['eventId1', 'eventId2'];
        $keyword = new Keyword('awesome');

        $this->scenario
            ->withAggregateId($ids[0])
            ->given(
                [
                    new EventCreated($ids[0])
                ]
            )
            ->withAggregateId($ids[1])
            ->given(
                [
                    new EventCreated($ids[1])
                ]
            )
            ->when(new TagEvents($ids, $keyword))
            ->then(
                [
                    new EventWasTagged($ids[0], $keyword),
                    new EventWasTagged($ids[1], $keyword)
                ]
            );
    }

    /**
     * @test
     */
    public function it_can_tag_all_results_of_a_search_query()
    {
        $events = [];
        $expectedSourcedEvents = [];
        $total = 60;
        $keyword = new Keyword('foo');

        for ($i = 1; $i <= $total; $i++) {
            $events[] = array(
                '@id' => 'http://example.com/event/' . $i,
            );

            $expectedSourcedEvents[] = new EventWasTagged($i, $keyword);

            $this->scenario
                ->withAggregateId($i)
                ->given(
                    [
                        new EventCreated($i)
                    ]
                );
        }

        $this->search->expects($this->any())
            ->method('search')
            ->with('*.*')
            ->will(
                $this->returnCallback(
                    function ($query, $limit, $start) use ($events) {
                        $pageEvents = array_slice($events, $start, $limit);

                        return [
                            'member' => $pageEvents,
                            'totalItems' => count($events),
                            'itemsPerPage' => $limit
                        ];
                    }
                )
            );

        $this->scenario
            ->when(new TagQuery('*.*', $keyword))
            ->then(
                $expectedSourcedEvents
            );
    }

    /**
     * @test
     */
    public function it_does_not_tag_events_when_a_search_error_occurs()
    {
        $this->search->expects($this->once())
            ->method('search')
            ->will(
                $this->throwException(
                    new \Guzzle\Http\Exception\ClientErrorResponseException()
                )
            );

        $this->scenario
            ->when(new TagQuery('---fsdfs', new Keyword('foo')))
            ->then(
                []
            );
    }

    /**
     * @test
     */
    public function it_can_translate_the_title_of_an_event()
    {
        $id = '1';
        $title = 'Voorbeeld';
        $language = new Language('nl');
        $this->scenario
            ->withAggregateId($id)
            ->given(
                [
                    new EventCreated($id)
                ]
            )
            ->when(new TranslateTitle($id, $language, $title))
            ->then(
                [
                    new TitleTranslated($id, $language, $title)
                ]
            );
    }
}
