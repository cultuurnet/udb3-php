<?php

namespace CultuurNet\UDB3\Organizer;

use Broadway\CommandHandling\CommandHandlerInterface;
use Broadway\CommandHandling\Testing\CommandHandlerScenarioTestCase;
use Broadway\EventHandling\EventBusInterface;
use Broadway\EventStore\EventStoreInterface;
use Broadway\EventStore\InMemoryEventStore;
use Broadway\EventStore\TraceableEventStore;
use CultuurNet\UDB3\ContactPoint;
use CultuurNet\UDB3\Address\Address;
use CultuurNet\UDB3\Address\Locality;
use CultuurNet\UDB3\Address\PostalCode;
use CultuurNet\UDB3\Address\Street;
use CultuurNet\UDB3\Offer\Commands\AbstractDeleteOrganizer;
use CultuurNet\UDB3\Organizer\Commands\AddLabel;
use CultuurNet\UDB3\Organizer\Commands\DeleteOrganizer;
use CultuurNet\UDB3\Organizer\Commands\RemoveLabel;
use CultuurNet\UDB3\Organizer\Events\LabelAdded;
use CultuurNet\UDB3\Organizer\Events\LabelRemoved;
use CultuurNet\UDB3\Organizer\Events\OrganizerCreated;
use CultuurNet\UDB3\Organizer\Events\OrganizerDeleted;
use CultuurNet\UDB3\Title;
use ValueObjects\Identity\UUID;
use ValueObjects\Web\Url;
use ValueObjects\Geography\Country;

class OrganizerCommandHandlerTest extends CommandHandlerScenarioTestCase
{
    /**
     * @var Title
     */
    private $defaultTitle;

    /**
     * @var TraceableEventStore
     */
    private $eventStore;

    /**
     * @var EventBusInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $eventBus;

    /**
     * @var OrganizerRepository
     */
    private $repository;

    /**
     * @var OrganizerRelationServiceInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $eventOrganizerRelationService;

    /**
     * @var OrganizerRelationServiceInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $placeOrganizerRelationService;

    /**
     * @var OrganizerCommandHandler
     */
    private $commandHandler;

    /**
     * @var OrganizerCreated
     */
    private $organizerCreated;

    public function setUp()
    {
        $this->defaultTitle = new Title('Sample');

        $this->eventStore = new TraceableEventStore(
            new InMemoryEventStore()
        );
        $this->eventBus = $this->getMock(EventBusInterface::class);
        $this->repository = new OrganizerRepository($this->eventStore, $this->eventBus);

        $this->eventOrganizerRelationService = $this->getMock(OrganizerRelationServiceInterface::class);
        $this->placeOrganizerRelationService = $this->getMock(OrganizerRelationServiceInterface::class);

        $this->commandHandler = (new OrganizerCommandHandler($this->repository))
            ->withOrganizerRelationService($this->eventOrganizerRelationService)
            ->withOrganizerRelationService($this->placeOrganizerRelationService);

        $this->organizerCreated = new OrganizerCreated(
            new UUID(),
            new Title('Organizer Title'),
            [new Address(
                new Street('Kerkstraat 69'),
                new PostalCode('9630'),
                new Locality('Zottegem'),
                Country::fromNative('BE')
            )],
            ['phone'],
            ['email'],
            ['url']
        );

        parent::setUp();
    }

    /**
     * Create a command handler for the given scenario test case.
     *
     * @param EventStoreInterface $eventStore
     * @param EventBusInterface $eventBus
     *
     * @return CommandHandlerInterface
     */
    protected function createCommandHandler(
        EventStoreInterface $eventStore,
        EventBusInterface $eventBus
    ) {
        return new OrganizerCommandHandler(new OrganizerRepository(
            $eventStore,
            $eventBus
        ));
    }

    /**
     * @test
     */
    public function it_handles_add_label()
    {
        $organizerId = $this->organizerCreated->getOrganizerId();
        $labelId = new UUID();

        $this->scenario
            ->withAggregateId($organizerId)
            ->given([$this->organizerCreated])
            ->when(new AddLabel($organizerId, $labelId))
            ->then([new LabelAdded($organizerId, $labelId)]);
    }

    /**
     * @test
     */
    public function it_does_not_add_the_same_label_twice()
    {
        $organizerId = $this->organizerCreated->getOrganizerId();
        $labelId = new UUID();

        $this->scenario
            ->withAggregateId($organizerId)
            ->given([
                $this->organizerCreated,
                new LabelAdded($organizerId, $labelId)
            ])
            ->when(new AddLabel($organizerId, $labelId))
            ->then([]);
    }

    /**
     * @test
     */
    public function it_removes_an_attached_label()
    {
        $organizerId = $this->organizerCreated->getOrganizerId();
        $labelId = new UUID();

        $this->scenario
            ->withAggregateId($organizerId)
            ->given([
                $this->organizerCreated,
                new LabelAdded($organizerId, $labelId)
            ])
            ->when(new RemoveLabel($organizerId, $labelId))
            ->then([new LabelRemoved($organizerId, $labelId)]);
    }

    /**
     * @test
     */
    public function it_does_not_remove_a_missing_label()
    {
        $organizerId = $this->organizerCreated->getOrganizerId();
        $labelId = new UUID();

        $this->scenario
            ->withAggregateId($organizerId)
            ->given([$this->organizerCreated])
            ->when(new RemoveLabel($organizerId, $labelId))
            ->then([]);
    }

    /**
     * @test
     */
    public function it_can_handle_complex_label_scenario()
    {
        $organizerId = $this->organizerCreated->getOrganizerId();
        $labelId1 = new UUID();
        $labelId2 = new UUID();

        $this->scenario
            ->withAggregateId($organizerId)
            ->given([$this->organizerCreated])
            ->when(new AddLabel($organizerId, $labelId1))
            ->when(new AddLabel($organizerId, $labelId2))
            ->when(new AddLabel($organizerId, $labelId2))
            ->when(new RemoveLabel($organizerId, $labelId1))
            ->when(new RemoveLabel($organizerId, $labelId2))
            ->when(new RemoveLabel($organizerId, $labelId2))
            ->then([
                new LabelAdded($organizerId, $labelId1),
                new LabelAdded($organizerId, $labelId2),
                new LabelRemoved($organizerId, $labelId1),
                new LabelRemoved($organizerId, $labelId2)
            ]);
    }

    /**
     * @test
     */
    public function it_handles_delete_commands()
    {
        $organizerId = $this->organizerCreated->getOrganizerId();

        $this->scenario->withAggregateId($organizerId)
            ->given([$this->organizerCreated])
            ->when(new DeleteOrganizer($organizerId))
            ->then([new OrganizerDeleted($organizerId)]);
    }

    /**
     * @test
     * @dataProvider deleteFromOfferDataProvider
     *
     * @param AbstractDeleteOrganizer $deleteOrganizer
     */
    public function it_ignores_delete_from_offer_commands(AbstractDeleteOrganizer $deleteOrganizer)
    {
        $organizerId = $this->organizerCreated->getOrganizerId();

        $this->scenario->withAggregateId($organizerId)
            ->given([$this->organizerCreated])
            ->when($deleteOrganizer)
            ->then([]);
    }

    /**
     * @return array
     */
    public function deleteFromOfferDataProvider()
    {
        return [
            [
                new \CultuurNet\UDB3\Place\Commands\DeleteOrganizer('place-id', 'organizer-id'),
            ],
            [
                new \CultuurNet\UDB3\Event\Commands\DeleteOrganizer('place-id', 'organizer-id'),
            ],
        ];
    }
}
