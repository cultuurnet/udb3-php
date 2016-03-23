<?php

namespace CultuurNet\UDB3\Place;

use Broadway\CommandHandling\Testing\CommandHandlerScenarioTestCase;
use Broadway\EventHandling\EventBusInterface;
use Broadway\EventStore\EventStoreInterface;
use CultuurNet\UDB3\Address;
use CultuurNet\UDB3\Calendar;
use CultuurNet\UDB3\Event\EventType;
use CultuurNet\UDB3\Facility;
use CultuurNet\UDB3\Label;
use CultuurNet\UDB3\Language;
use CultuurNet\UDB3\Place\CommandHandler;
use CultuurNet\UDB3\Place\Commands\AddLabel;
use CultuurNet\UDB3\Place\Commands\DeleteLabel;
use CultuurNet\UDB3\Place\Commands\DeletePlace;
use CultuurNet\UDB3\Place\Commands\TranslateDescription;
use CultuurNet\UDB3\Place\Commands\TranslateTitle;
use CultuurNet\UDB3\Place\Commands\UpdateFacilities;
use CultuurNet\UDB3\Place\Commands\UpdateMajorInfo;
use CultuurNet\UDB3\Place\Events\DescriptionTranslated;
use CultuurNet\UDB3\Place\Events\FacilitiesUpdated;
use CultuurNet\UDB3\Place\Events\LabelAdded;
use CultuurNet\UDB3\Place\Events\LabelDeleted;
use CultuurNet\UDB3\Place\Events\MajorInfoUpdated;
use CultuurNet\UDB3\Place\Events\PlaceCreated;
use CultuurNet\UDB3\Place\Events\PlaceDeleted;
use CultuurNet\UDB3\Place\Events\TitleTranslated;
use CultuurNet\UDB3\Search\SearchServiceInterface;
use CultuurNet\UDB3\Title;
use PHPUnit_Framework_MockObject_MockObject;
use ValueObjects\String\String;

class PlaceHandlerTest extends CommandHandlerScenarioTestCase
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
        $repository = new PlaceRepository(
            $eventStore,
            $eventBus
        );

        return new CommandHandler($repository, $this->search);
    }

    private function factorOfferCreated($id)
    {
        return new PlaceCreated(
            $id,
            new Title('some representative title'),
            new EventType('0.50.4.0.0', 'concert'),
            new Address('$street', '$postalcode', '$locality', '$country'),
            new Calendar('permanent', '', '')
        );
    }

    /**
     * @test
     */
    public function it_can_update_major_info_of_a_place()
    {
        $id = '1';
        $title = new Title('foo');
        $eventType = new EventType('0.50.4.0.0', 'concert');
        $address = new Address('$street', '$postalcode', '$locality', '$country');
        $calendar = new Calendar('permanent', '', '');

        $this->scenario
            ->withAggregateId($id)
            ->given(
                [$this->factorOfferCreated($id)]
            )
            ->when(
                new UpdateMajorInfo($id, $title, $eventType, $address, $calendar)
            )
            ->then([new MajorInfoUpdated($id, $title, $eventType, $address, $calendar)]);
    }

    /**
     * @test
     */
    public function it_can_update_facilities_of_a_place()
    {
        $id = '1';
        $facilities = [
            new Facility('facility1', 'facility label'),
        ];

        $this->scenario
            ->withAggregateId($id)
            ->given(
                [$this->factorOfferCreated($id)]
            )
            ->when(
                new UpdateFacilities($id, $facilities)
            )
            ->then([new FacilitiesUpdated($id, $facilities)]);
    }

    /**
     * @test
     */
    public function it_can_delete_places()
    {
        $id = '1';
        $this->scenario
            ->withAggregateId($id)
            ->given(
                [$this->factorOfferCreated($id)]
            )
            ->when(
                new DeletePlace($id)
            )
            ->then([new PlaceDeleted($id)]);
    }

    /**
     * @test
     */
    public function it_can_label_a_place()
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
    public function it_can_unlabel_a_place()
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
    public function it_does_not_remove_a_label_that_is_not_present_on_a_place()
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
    public function it_does_not_remove_a_label_from_a_place_that_has_been_unlabelled_already()
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
    public function it_can_translate_the_title_of_an_event()
    {
        $id = '1';
        $title = new String('Voorbeeld');
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
        $description = new String('Lorem ipsum dolor si amet...');
        $language = new Language('nl');
        $this->scenario
            ->withAggregateId($id)
            ->given(
                [$this->factorOfferCreated($id)]
            )
            ->when(new TranslateDescription($id, $language, $description))
            ->then([new DescriptionTranslated($id, $language, $description)]);
    }
}
