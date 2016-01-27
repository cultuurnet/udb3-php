<?php

namespace CultuurNet\UDB3\Event;

use Broadway\CommandHandling\Testing\CommandHandlerScenarioTestCase;
use Broadway\EventHandling\EventBusInterface;
use Broadway\EventStore\EventStoreInterface;
use CultuurNet\UDB3\Calendar;
use CultuurNet\UDB3\Event\Commands\AddLabel;
use CultuurNet\UDB3\Event\Commands\DeleteEvent;
use CultuurNet\UDB3\Event\Commands\LabelEvents;
use CultuurNet\UDB3\Event\Commands\LabelQuery;
use CultuurNet\UDB3\Event\Commands\DeleteLabel;
use CultuurNet\UDB3\Event\Commands\UpdateMajorInfo;
use CultuurNet\UDB3\Event\Events\EventCreated;
use CultuurNet\UDB3\Event\Events\EventDeleted;
use CultuurNet\UDB3\Event\Events\LabelAdded;
use CultuurNet\UDB3\Event\Events\MajorInfoUpdated;
use CultuurNet\UDB3\Event\Events\LabelDeleted;
use CultuurNet\UDB3\Label;
use CultuurNet\UDB3\Language;
use CultuurNet\UDB3\Location;
use CultuurNet\UDB3\Search\Results;
use CultuurNet\UDB3\Search\SearchServiceInterface;
use CultuurNet\UDB3\Title;
use Guzzle\Http\Exception\ClientErrorResponseException;
use PHPUnit_Framework_MockObject_MockObject;
use ValueObjects\Number\Integer;

class EventCommandHandlerTest extends CommandHandlerScenarioTestCase
{

    use \CultuurNet\UDB3\OfferCommandHandlerTestTrait;

    /**
     * @var SearchServiceInterface|PHPUnit_Framework_MockObject_MockObject
     */
    protected $search;

    public function setUp()
    {
        $this->search = $this->getMock(SearchServiceInterface::class);

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

    private function factorOfferCreated($id)
    {
        return new EventCreated(
            $id,
            new Title('some representative title'),
            new EventType('0.50.4.0.0', 'concert'),
            new Location('LOCATION-ABC-123', '$name', '$country', '$locality', '$postalcode', '$street'),
            new Calendar('permanent', '', '')
        );
    }

    /**
     * @test
     */
    public function it_can_label_a_list_of_events_with_a_label()
    {
        $ids = ['eventId1', 'eventId2'];

        $this->scenario
            ->withAggregateId($ids[0])
            ->given(
                [
                    $this->factorOfferCreated($ids[0])
                ]
            )
            ->withAggregateId($ids[1])
            ->given(
                [
                    $this->factorOfferCreated($ids[1])
                ]
            )
            ->when(new LabelEvents($ids, new Label('awesome')))
            ->then(
                [
                    new LabelAdded($ids[0], new Label('awesome')),
                    new LabelAdded($ids[1], new Label('awesome'))
                ]
            );
    }

    /**
     * @test
     */
    public function it_can_label_all_results_of_a_search_query()
    {
        $events = [];
        $expectedSourcedEvents = [];
        $total = 60;

        for ($i = 1; $i <= $total; $i++) {
            $eventId = (string)$i;
            $events[] = array(
                '@id' => 'http://example.com/event/' . $eventId,
            );

            $expectedSourcedEvents[] = new LabelAdded($eventId, new Label('foo'));

            $this->scenario
                ->withAggregateId($i)
                ->given(
                    [
                        $this->factorOfferCreated($eventId)
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
                        $totalItemCount = new Integer(count($events));
                        $results = new Results($pageEvents, $totalItemCount);

                        return $results;
                    }
                )
            );

        $this->scenario
            ->when(new LabelQuery('*.*', new Label('foo')))
            ->then(
                $expectedSourcedEvents
            );
    }

    /**
     * @test
     */
    public function it_does_not_label_events_when_a_search_error_occurs()
    {
        $this->search->expects($this->once())
            ->method('search')
            ->will(
                $this->throwException(
                    new ClientErrorResponseException()
                )
            );

        $this->setExpectedException(ClientErrorResponseException::class);

        $this->scenario
            ->when(new LabelQuery('---fsdfs', new Label('foo')))
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
                    $this->factorOfferCreated($id)
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
                [$this->factorOfferCreated($id)]
            )
            ->when(new TranslateDescription($id, $language, $description))
            ->then([new DescriptionTranslated($id, $language, $description)]);
    }

    /**
     * @test
     */
    public function it_can_label_an_event()
    {
        $id = '1';
        $this->scenario
            ->withAggregateId($id)
            ->given(
                [$this->factorOfferCreated($id)]
            )
            ->when(new AddLabel($id, new Label('foo')))
            ->then([new LabelAdded($id, new Label('foo'))]);
    }

    /**
     * @test
     */
    public function it_can_unlabel_an_event()
    {
        $id = '1';
        $this->scenario
            ->withAggregateId($id)
            ->given(
                [
                    $this->factorOfferCreated($id),
                    new LabelAdded($id, new Label('foo'))
                ]
            )
            ->when(new DeleteLabel($id, new Label('foo')))
            ->then([new LabelDeleted($id, new Label('foo'))]);
    }

    /**
     * @test
     */
    public function it_does_not_remove_a_label_that_is_not_present_on_an_event()
    {
        $id = '1';
        $this->scenario
            ->withAggregateId($id)
            ->given(
                [$this->factorOfferCreated($id)]
            )
            ->when(new DeleteLabel($id, new Label('foo')))
            ->then([]);
    }

    /**
     * @test
     */
    public function it_does_not_remove_a_label_from_an_event_that_has_been_unlabelled_already()
    {
        $id = '1';
        $this->scenario
            ->withAggregateId($id)
            ->given(
                [
                    $this->factorOfferCreated($id),
                    new LabelAdded($id, new Label('foo')),
                    new LabelDeleted($id, new Label('foo'))
                ]
            )
            ->when(new DeleteLabel($id, new Label('foo')))
            ->then([]);
    }

    /**
     * @test
     */
    public function it_can_update_major_info_of_an_event()
    {
        $id = '1';
        $title = new Title('foo');
        $eventType = new EventType('0.50.4.0.0', 'concert');
        $location = new Location('LOCATION-ABC-123', '$name', '$country', '$locality', '$postalcode', '$street');
        $calendar = new Calendar('permanent', '', '');

        $this->scenario
            ->withAggregateId($id)
            ->given(
                [$this->factorOfferCreated($id)]
            )
            ->when(
                new UpdateMajorInfo($id, $title, $eventType, $location, $calendar)
            )
            ->then([new MajorInfoUpdated($id, $title, $eventType, $location, $calendar)]);
    }

    /**
     * @test
     */
    public function it_can_delete_events()
    {
        $id = '1';
        $this->scenario
            ->withAggregateId($id)
            ->given(
                [$this->factorOfferCreated($id)]
            )
            ->when(
                new DeleteEvent($id)
            )
            ->then([new EventDeleted($id)]);
    }
}
