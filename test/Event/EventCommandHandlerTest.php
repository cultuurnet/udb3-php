<?php

namespace CultuurNet\UDB3\Event;

use Broadway\CommandHandling\Testing\CommandHandlerScenarioTestCase;
use Broadway\EventHandling\EventBusInterface;
use Broadway\EventStore\EventStoreInterface;
use Broadway\Repository\RepositoryInterface;
use CultuurNet\UDB3\Address\Address;
use CultuurNet\UDB3\Address\Locality;
use CultuurNet\UDB3\Address\PostalCode;
use CultuurNet\UDB3\Address\Street;
use CultuurNet\UDB3\Calendar;
use CultuurNet\UDB3\CalendarType;
use CultuurNet\UDB3\Description;
use CultuurNet\UDB3\Event\Commands\AddLabel;
use CultuurNet\UDB3\Event\Commands\DeleteEvent;
use CultuurNet\UDB3\Event\Commands\RemoveLabel;
use CultuurNet\UDB3\Event\Commands\EventCommandFactory;
use CultuurNet\UDB3\Event\Commands\UpdateAudience;
use CultuurNet\UDB3\Event\Commands\UpdateDescription;
use CultuurNet\UDB3\Event\Commands\UpdateLocation;
use CultuurNet\UDB3\Event\Commands\UpdateMajorInfo;
use CultuurNet\UDB3\Event\Commands\UpdateTitle;
use CultuurNet\UDB3\Event\Events\AudienceUpdated;
use CultuurNet\UDB3\Event\Events\DescriptionTranslated;
use CultuurNet\UDB3\Event\Events\EventCreated;
use CultuurNet\UDB3\Event\Events\EventDeleted;
use CultuurNet\UDB3\Event\Events\LabelAdded;
use CultuurNet\UDB3\Event\Events\LabelRemoved;
use CultuurNet\UDB3\Event\Events\LocationUpdated;
use CultuurNet\UDB3\Event\Events\MajorInfoUpdated;
use CultuurNet\UDB3\Event\Events\PriceInfoUpdated;
use CultuurNet\UDB3\Event\Events\TitleTranslated;
use CultuurNet\UDB3\Event\ValueObjects\Audience;
use CultuurNet\UDB3\Event\ValueObjects\AudienceType;
use CultuurNet\UDB3\Label;
use CultuurNet\UDB3\Label\ReadModels\JSON\Repository\Entity;
use CultuurNet\UDB3\Label\ReadModels\JSON\Repository\ReadRepositoryInterface;
use CultuurNet\UDB3\Label\ValueObjects\Privacy;
use CultuurNet\UDB3\Label\ValueObjects\Visibility;
use CultuurNet\UDB3\Language;
use CultuurNet\UDB3\Location\Location;
use CultuurNet\UDB3\Location\LocationId;
use CultuurNet\UDB3\OfferCommandHandlerTestTrait;
use CultuurNet\UDB3\PriceInfo\BasePrice;
use CultuurNet\UDB3\PriceInfo\Price;
use CultuurNet\UDB3\PriceInfo\PriceInfo;
use CultuurNet\UDB3\Title;
use ValueObjects\Geography\Country;
use ValueObjects\Identity\UUID;
use ValueObjects\Money\Currency;
use ValueObjects\StringLiteral\StringLiteral;

class EventCommandHandlerTest extends CommandHandlerScenarioTestCase
{
    use OfferCommandHandlerTestTrait;

    /**
     * @var EventCommandFactory
     */
    private $commandFactory;

    protected function createCommandHandler(
        EventStoreInterface $eventStore,
        EventBusInterface $eventBus
    ) {
        $repository = new EventRepository(
            $eventStore,
            $eventBus
        );

        $this->organizerRepository = $this->createMock(RepositoryInterface::class);

        $this->labelRepository = $this->createMock(ReadRepositoryInterface::class);
        $this->labelRepository->method('getByName')
            ->will($this->returnCallback(
                function (StringLiteral $labelName) {
                    return new Entity(
                        new UUID(),
                        $labelName,
                        $labelName->toNative() === 'foo' ? Visibility::VISIBLE() : Visibility::INVISIBLE(),
                        Privacy::PRIVACY_PUBLIC()
                    );
                }
            ));

        $this->commandFactory = new EventCommandFactory();

        return new EventCommandHandler(
            $repository,
            $this->organizerRepository,
            $this->labelRepository
        );
    }

    private function factorOfferCreated($id)
    {
        return new EventCreated(
            $id,
            new Title('some representative title'),
            new EventType('0.50.4.0.0', 'concert'),
            new Location(
                'd0cd4e9d-3cf1-4324-9835-2bfba63ac015',
                new StringLiteral('Repeteerkot'),
                new Address(
                    new Street('Kerkstraat 69'),
                    new PostalCode('9630'),
                    new Locality('Zottegem'),
                    Country::fromNative('BE')
                )
            ),
            new Calendar(CalendarType::PERMANENT())
        );
    }

    /**
     * @test
     */
    public function it_can_translate_the_title_of_an_event_by_updating_with_a_foreign_language()
    {
        $id = '1';
        $title = new Title('Voorbeeld');
        $language = new Language('fr');
        $this->scenario
            ->withAggregateId($id)
            ->given(
                [
                    $this->factorOfferCreated($id),
                ]
            )
            ->when(new UpdateTitle($id, $language, $title))
            ->then(
                [
                    new TitleTranslated($id, $language, $title),
                ]
            );
    }

    /**
     * @test
     */
    public function it_can_translate_the_description_of_an_event_by_updating_with_a_foreign_language()
    {
        $id = '1';
        $description = new Description('Lorem ipsum dolor si amet...');
        $language = new Language('fr');
        $this->scenario
            ->withAggregateId($id)
            ->given(
                [$this->factorOfferCreated($id)]
            )
            ->when(new UpdateDescription($id, $language, $description))
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
    public function it_can_label_an_event_with_invisible_label()
    {
        $id = '1';
        $this->scenario
            ->withAggregateId($id)
            ->given(
                [$this->factorOfferCreated($id)]
            )
            ->when(new AddLabel($id, new Label('bar')))
            ->then([new LabelAdded($id, new Label('bar', false))]);
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
                    new LabelAdded($id, new Label('foo')),
                ]
            )
            ->when(new RemoveLabel($id, new Label('foo')))
            ->then([new LabelRemoved($id, new Label('foo'))]);
    }

    /**
     * @test
     */
    public function it_can_unlabel_an_event_with_invisible_label()
    {
        $id = '1';
        $this->scenario
            ->withAggregateId($id)
            ->given(
                [
                    $this->factorOfferCreated($id),
                    new LabelAdded($id, new Label('bar', false)),
                ]
            )
            ->when(new RemoveLabel($id, new Label('bar')))
            ->then([new LabelRemoved($id, new Label('bar', false))]);
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
            ->when(new RemoveLabel($id, new Label('foo')))
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
                    new LabelRemoved($id, new Label('foo')),
                ]
            )
            ->when(new RemoveLabel($id, new Label('foo')))
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
        $location = new Location(
            'd0cd4e9d-3cf1-4324-9835-2bfba63ac015',
            new StringLiteral('Repeteerkot'),
            new Address(
                new Street('Kerkstraat 69'),
                new PostalCode('9630'),
                new Locality('Zottegem'),
                Country::fromNative('BE')
            )
        );
        $calendar = new Calendar(CalendarType::PERMANENT());

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
    public function it_can_update_location_of_an_event()
    {
        $eventId = '3ed90f18-93a3-4340-981d-12e57efa0211';

        $locationId = new LocationId('57738178-28a5-4afb-90c0-fd0beba172a8');

        $this->scenario
            ->withAggregateId($eventId)
            ->given(
                [
                    $this->factorOfferCreated($eventId),
                ]
            )
            ->when(
                new UpdateLocation($eventId, $locationId)
            )
            ->then(
                [
                    new LocationUpdated($eventId, $locationId),
                ]
            );
    }

    /**
     * @test
     */
    public function it_can_update_audience()
    {
        $eventId = 'd2b41f1d-598c-46af-a3a5-10e373faa6fe';

        $this->scenario
            ->withAggregateId($eventId)
            ->given(
                [
                    $this->factorOfferCreated($eventId),
                ]
            )
            ->when(
                new UpdateAudience(
                    $eventId,
                    new Audience(AudienceType::EDUCATION())
                )
            )
            ->then(
                [
                    new AudienceUpdated(
                        $eventId,
                        new Audience(AudienceType::EDUCATION())
                    ),
                ]
            );
    }

    /**
     * @test
     */
    public function it_should_ignore_updating_the_audience_when_the_same_audience_type_is_already_set()
    {
        $eventId = 'd2b41f1d-598c-46af-a3a5-10e373faa6fe';

        $this->scenario
            ->withAggregateId($eventId)
            ->given(
                [
                    $this->factorOfferCreated($eventId),
                    new AudienceUpdated(
                        $eventId,
                        new Audience(AudienceType::EDUCATION())
                    ),
                ]
            )
            ->when(
                new UpdateAudience(
                    $eventId,
                    new Audience(AudienceType::EDUCATION())
                )
            )
            ->then([]);
    }

    /**
     * @test
     */
    public function it_can_update_price_info()
    {
        $id = '1';

        $priceInfo = new PriceInfo(
            new BasePrice(
                Price::fromFloat(10.5),
                Currency::fromNative('EUR')
            )
        );

        $this->scenario
            ->withAggregateId($id)
            ->given(
                [
                    $this->factorOfferCreated($id),
                ]
            )
            ->when(
                $this->commandFactory->createUpdatePriceInfoCommand($id, $priceInfo)
            )
            ->then(
                [
                    new PriceInfoUpdated($id, $priceInfo),
                ]
            );
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
