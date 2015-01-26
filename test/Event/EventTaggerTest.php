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

        $this->scenario
            ->withAggregateId($ids[0])
            ->given(
                [
                    new EventCreated($ids[0], 'some representative title', 'LOCATION-ABC-123', new \DateTime())
                ]
            )
            ->withAggregateId($ids[1])
            ->given(
                [
                    new EventCreated($ids[1], 'another representative title', 'LOCATION-ABC-123', new \DateTime())
                ]
            )
            ->when(new TagEvents($ids, new Keyword('awesome')))
            ->then(
                [
                    new EventWasTagged($ids[0], new Keyword('awesome')),
                    new EventWasTagged($ids[1], new Keyword('awesome'))
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

        for ($i = 1; $i <= $total; $i++) {
            $events[] = array(
                '@id' => 'http://example.com/event/' . $i,
            );

            $expectedSourcedEvents[] = new EventWasTagged($i, new Keyword('foo'));

            $this->scenario
                ->withAggregateId($i)
                ->given(
                    [
                        new EventCreated($i, 'some representative title', 'LOCATION-ABC-123', new \DateTime())
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
            ->when(new TagQuery('*.*', new Keyword('foo')))
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
                    new EventCreated($id, 'some representative title', 'LOCATION-ABC-123', new \DateTime())
                ]
            )
            ->when(new TranslateTitle($id, $language, $title))
            ->then(
                [
                    new TitleTranslated($id, $language, $title)
                ]
            );
    }

    /**
     * @test
     */
    public function it_can_translate_the_description_of_an_event()
    {
        $id = '1';
        $description = 'Lorem ipsum dolor si amet...';
        $language = new Language('nl');
        $this->scenario
            ->withAggregateId($id)
            ->given(
                [new EventCreated($id, 'some representative title', 'LOCATION-ABC-123', new \DateTime())]
            )
            ->when(new TranslateDescription($id, $language, $description))
            ->then([new DescriptionTranslated($id, $language, $description)]);
    }

    /**
     * @test
     */
    public function it_can_tag_an_event()
    {
        $id = '1';
        $this->scenario
            ->withAggregateId($id)
            ->given(
                [new EventCreated($id,'some representative title', 'LOCATION-ABC-123', new \DateTime())]
            )
            ->when(new Tag($id, new Keyword('foo')))
            ->then([new EventWasTagged($id, new Keyword('foo'))]);
    }

    /**
     * @test
     */
    public function it_can_erase_a_tag_from_an_event()
    {
        $id = '1';
        $this->scenario
            ->withAggregateId($id)
            ->given(
                [
                    new EventCreated($id, 'some representative title', 'LOCATION-ABC-123', new \DateTime()),
                    new EventWasTagged($id, new Keyword('foo'))
                ]
            )
            ->when(new EraseTag($id, new Keyword('foo')))
            ->then([new TagErased($id, new Keyword('foo'))]);
    }

    /**
     * @test
     */
    public function it_does_not_erase_a_tag_that_is_not_present_on_an_event()
    {
        $id = '1';
        $this->scenario
            ->withAggregateId($id)
            ->given(
                [new EventCreated($id, 'some representative title', 'LOCATION-ABC-123', new \DateTime())]
            )
            ->when(new EraseTag($id, new Keyword('foo')))
            ->then([]);
    }

    /**
     * @test
     */
    public function it_does_not_erase_a_tag_from_an_event_that_has_been_erased_already(
    )
    {
        $id = '1';
        $this->scenario
            ->withAggregateId($id)
            ->given(
                [
                    new EventCreated($id, 'some representative title', 'LOCATION-ABC-123', new \DateTime()),
                    new EventWasTagged($id, new Keyword('foo')),
                    new TagErased($id, new Keyword('foo'))
                ]
            )
            ->when(new EraseTag($id, new Keyword('foo')))
            ->then([]);
    }
}
