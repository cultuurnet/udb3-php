<?php

namespace CultuurNet\UDB3\Event;

use Broadway\CommandHandling\Testing\CommandHandlerScenarioTestCase;
use Broadway\EventHandling\EventBusInterface;
use Broadway\EventStore\EventStoreInterface;
use CultuurNet\UDB3\BookingInfo;
use CultuurNet\UDB3\Calendar;
use CultuurNet\UDB3\ContactPoint;
use CultuurNet\UDB3\Event\Commands\AddImage;
use CultuurNet\UDB3\Event\Commands\ApplyLabel;
use CultuurNet\UDB3\Event\Commands\DeleteEvent;
use CultuurNet\UDB3\Event\Commands\DeleteImage;
use CultuurNet\UDB3\Event\Commands\DeleteOrganizer;
use CultuurNet\UDB3\Event\Commands\LabelEvents;
use CultuurNet\UDB3\Event\Commands\LabelQuery;
use CultuurNet\UDB3\Event\Commands\Unlabel;
use CultuurNet\UDB3\Event\Commands\UpdateBookingInfo;
use CultuurNet\UDB3\Event\Commands\UpdateContactPoint;
use CultuurNet\UDB3\Event\Commands\UpdateDescription;
use CultuurNet\UDB3\Event\Commands\UpdateImage;
use CultuurNet\UDB3\Event\Commands\UpdateMajorInfo;
use CultuurNet\UDB3\Event\Commands\UpdateOrganizer;
use CultuurNet\UDB3\Event\Commands\UpdateTypicalAgeRange;
use CultuurNet\UDB3\Event\Events\BookingInfoUpdated;
use CultuurNet\UDB3\Event\Events\ContactPointUpdated;
use CultuurNet\UDB3\Event\Events\DescriptionUpdated;
use CultuurNet\UDB3\Event\Events\EventCreated;
use CultuurNet\UDB3\Event\Events\EventDeleted;
use CultuurNet\UDB3\Event\Events\EventWasLabelled;
use CultuurNet\UDB3\Event\Events\ImageAdded;
use CultuurNet\UDB3\Event\Events\ImageDeleted;
use CultuurNet\UDB3\Event\Events\ImageUpdated;
use CultuurNet\UDB3\Event\Events\MajorInfoUpdated;
use CultuurNet\UDB3\Event\Events\OrganizerDeleted;
use CultuurNet\UDB3\Event\Events\OrganizerUpdated;
use CultuurNet\UDB3\Event\Events\TypicalAgeRangeUpdated;
use CultuurNet\UDB3\Event\Events\Unlabelled;
use CultuurNet\UDB3\Label;
use CultuurNet\UDB3\Language;
use CultuurNet\UDB3\Location;
use CultuurNet\UDB3\MediaObject;
use CultuurNet\UDB3\Search\SearchServiceInterface;
use CultuurNet\UDB3\Title;
use Guzzle\Http\Exception\ClientErrorResponseException;
use PHPUnit_Framework_MockObject_MockObject;

class EventCommandHandlerTest extends CommandHandlerScenarioTestCase
{
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

    private function factorEventCreated($id)
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
                    $this->factorEventCreated($ids[0])
                ]
            )
            ->withAggregateId($ids[1])
            ->given(
                [
                    $this->factorEventCreated($ids[1])
                ]
            )
            ->when(new LabelEvents($ids, new Label('awesome')))
            ->then(
                [
                    new EventWasLabelled($ids[0], new Label('awesome')),
                    new EventWasLabelled($ids[1], new Label('awesome'))
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
            $events[] = array(
                '@id' => 'http://example.com/event/' . $i,
            );

            $expectedSourcedEvents[] = new EventWasLabelled($i, new Label('foo'));

            $this->scenario
                ->withAggregateId($i)
                ->given(
                    [
                        $this->factorEventCreated($i)
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
                    $this->factorEventCreated($id)
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
                [$this->factorEventCreated($id)]
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
                [$this->factorEventCreated($id)]
            )
            ->when(new ApplyLabel($id, new Label('foo')))
            ->then([new EventWasLabelled($id, new Label('foo'))]);
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
                    $this->factorEventCreated($id),
                    new EventWasLabelled($id, new Label('foo'))
                ]
            )
            ->when(new Unlabel($id, new Label('foo')))
            ->then([new Unlabelled($id, new Label('foo'))]);
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
                [$this->factorEventCreated($id)]
            )
            ->when(new Unlabel($id, new Label('foo')))
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
                    $this->factorEventCreated($id),
                    new EventWasLabelled($id, new Label('foo')),
                    new Unlabelled($id, new Label('foo'))
                ]
            )
            ->when(new Unlabel($id, new Label('foo')))
            ->then([]);
    }

    /**
     * @test
     */
    public function it_can_update_booking_info_of_an_event()
    {
        $id = '1';
        $bookingInfo = new BookingInfo();
        $this->scenario
            ->withAggregateId($id)
            ->given(
                [$this->factorEventCreated($id)]
            )
            ->when(
              new UpdateBookingInfo($id, $bookingInfo))
            ->then([new BookingInfoUpdated($id, $bookingInfo)]);
    }

    /**
     * @test
     */
    public function it_can_update_contact_point_of_an_event()
    {
        $id = '1';
        $contactPoint = new ContactPoint();
        $this->scenario
            ->withAggregateId($id)
            ->given(
                [$this->factorEventCreated($id)]
            )
            ->when(
              new UpdateContactPoint($id, $contactPoint))
            ->then([new ContactPointUpdated($id, $contactPoint)]);
    }

    /**
     * @test
     */
    public function it_can_update_description_of_an_event()
    {
        $id = '1';
        $description = 'foo';
        $this->scenario
            ->withAggregateId($id)
            ->given(
                [$this->factorEventCreated($id)]
            )
            ->when(
              new UpdateDescription($id, $description))
            ->then([new DescriptionUpdated($id, $description)]);
    }

    /**
     * @test
     */
    public function it_can_add_an_image_to_an_event()
    {
        $id = '1';
        $mediaObject = new MediaObject('$url', '$thumbnailUrl', '$description', '$copyrightHolder');
        $this->scenario
            ->withAggregateId($id)
            ->given(
                [$this->factorEventCreated($id)]
            )
            ->when(
              new AddImage($id, $mediaObject))
            ->then([new ImageAdded($id, $mediaObject)]);
    }

    /**
     * @test
     */
    public function it_can_add_delete_an_image_of_an_event()
    {
        $id = '1';
        $indexToDelete = 1;
        $internalId = '1';
        $this->scenario
            ->withAggregateId($id)
            ->given(
                [$this->factorEventCreated($id)]
            )
            ->when(
              new DeleteImage($id, $indexToDelete, $internalId))
            ->then([new ImageDeleted($id, $indexToDelete, $internalId)]);
    }

    /**
     * @test
     */
    public function it_can_add_update_an_image_of_an_event()
    {
        $id = '1';
        $index = 1;
        $mediaObject = new MediaObject('$url', '$thumbnailUrl', '$description', '$copyrightHolder');
        $this->scenario
            ->withAggregateId($id)
            ->given(
                [$this->factorEventCreated($id)]
            )
            ->when(
              new UpdateImage($id, $index, $mediaObject))
            ->then([new ImageUpdated($id, $index, $mediaObject)]);
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
                [$this->factorEventCreated($id)]
            )
            ->when(
              new UpdateMajorInfo($id, $title, $eventType, $location, $calendar))
            ->then([new MajorInfoUpdated($id, $title, $eventType, $location, $calendar)]);
    }

    /**
     * @test
     */
    public function it_can_delete_organizer_of_an_event()
    {
        $id = '1';
        $organizerId = '5';
        $this->scenario
            ->withAggregateId($id)
            ->given(
                [$this->factorEventCreated($id)]
            )
            ->when(
              new DeleteOrganizer($id, $organizerId))
            ->then([new OrganizerDeleted($id, $organizerId)]);
    }

    /**
     * @test
     */
    public function it_can_update_organizer_of_an_event()
    {
        $id = '1';
        $organizer = '1';
        $this->scenario
            ->withAggregateId($id)
            ->given(
                [$this->factorEventCreated($id)]
            )
            ->when(
              new UpdateOrganizer($id, $organizer))
            ->then([new OrganizerUpdated($id, $organizer)]);
    }

    /**
     * @test
     */
    public function it_can_update_typical_agerange_of_an_event()
    {
        $id = '1';
        $ageRange = '-18';
        $this->scenario
            ->withAggregateId($id)
            ->given(
                [$this->factorEventCreated($id)]
            )
            ->when(
              new UpdateTypicalAgeRange($id, $ageRange))
            ->then([new TypicalAgeRangeUpdated($id, $ageRange)]);
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
                [$this->factorEventCreated($id)]
            )
            ->when(
              new DeleteEvent($id))
            ->then([new EventDeleted($id)]);
    }

}
