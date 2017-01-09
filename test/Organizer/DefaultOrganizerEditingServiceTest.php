<?php

namespace CultuurNet\UDB3\Organizer;

use Broadway\CommandHandling\CommandBusInterface;
use Broadway\EventHandling\SimpleEventBus;
use Broadway\EventStore\InMemoryEventStore;
use Broadway\EventStore\TraceableEventStore;
use Broadway\Repository\RepositoryInterface;
use Broadway\UuidGenerator\UuidGeneratorInterface;
use CultuurNet\UDB3\Address\Locality;
use CultuurNet\UDB3\Address\PostalCode;
use CultuurNet\UDB3\Address\Street;
use CultuurNet\UDB3\Label;
use CultuurNet\UDB3\Label\LabelServiceInterface;
use CultuurNet\UDB3\Label\ValueObjects\LabelName;
use CultuurNet\UDB3\Organizer\Commands\AddLabel;
use CultuurNet\UDB3\Address\Address;
use CultuurNet\UDB3\ContactPoint;
use CultuurNet\UDB3\Organizer\Commands\DeleteOrganizer;
use CultuurNet\UDB3\Organizer\Commands\RemoveLabel;
use CultuurNet\UDB3\Organizer\Events\AddressUpdated;
use CultuurNet\UDB3\Organizer\Events\ContactPointUpdated;
use ValueObjects\Geography\Country;
use CultuurNet\UDB3\Organizer\Events\OrganizerCreatedWithUniqueWebsite;
use CultuurNet\UDB3\Title;
use ValueObjects\Web\Url;

class DefaultOrganizerEditingServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CommandBusInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $commandBus;

    /**
     * @var UuidGeneratorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $uuidGenerator;

    /**
     * @var TraceableEventStore
     */
    protected $eventStore;

    /**
     * @var RepositoryInterface
     */
    private $organizerRepository;

    /**
     * @var LabelServiceInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $labelService;

    /**
     * @var DefaultOrganizerEditingService
     */
    private $service;

    public function setUp()
    {
        $this->commandBus = $this->createMock(CommandBusInterface::class);

        $this->uuidGenerator = $this->createMock(UuidGeneratorInterface::class);
        $this->uuidGenerator->method('generate')
            ->willReturn('9196cb78-4381-11e6-beb8-9e71128cae77');

        $this->eventStore = new TraceableEventStore(new InMemoryEventStore());

        $this->organizerRepository = new OrganizerRepository(
            $this->eventStore,
            new SimpleEventBus
        );

        $this->labelService = $this->createMock(LabelServiceInterface::class);

        $this->service = new DefaultOrganizerEditingService(
            $this->commandBus,
            $this->uuidGenerator,
            $this->organizerRepository,
            $this->labelService
        );
    }

    /**
     * @test
     */
    public function it_can_create_an_organizer_with_a_unique_website()
    {
        $this->eventStore->trace();

        $organizerId = $this->service->create(
            Url::fromNative('http://www.stuk.be'),
            new Title('Het Stuk')
        );

        $expectedUuid = '9196cb78-4381-11e6-beb8-9e71128cae77';

        $this->assertEquals(
            [
                new OrganizerCreatedWithUniqueWebsite(
                    '9196cb78-4381-11e6-beb8-9e71128cae77',
                    Url::fromNative('http://www.stuk.be'),
                    new Title('Het Stuk')
                ),
            ],
            $this->eventStore->getEvents()
        );

        $this->assertEquals($expectedUuid, $organizerId);
    }

    /**
     * @test
     */
    public function it_can_create_an_organizer_with_a_unique_website_plus_contact_point_and_address()
    {
        $this->eventStore->trace();

        $organizerId = $this->service->create(
            Url::fromNative('http://www.stuk.be'),
            new Title('Het Stuk'),
            new Address(
                new Street('Wetstraat 1'),
                new PostalCode('1000'),
                new Locality('Brussel'),
                Country::fromNative('BE')
            ),
            new ContactPoint(['050/123'], ['test@test.be', 'test2@test.be'], ['http://www.google.be'])
        );

        $expectedUuid = '9196cb78-4381-11e6-beb8-9e71128cae77';

        $this->assertEquals(
            [
                new OrganizerCreatedWithUniqueWebsite(
                    '9196cb78-4381-11e6-beb8-9e71128cae77',
                    Url::fromNative('http://www.stuk.be'),
                    new Title('Het Stuk')
                ),
                new AddressUpdated(
                    '9196cb78-4381-11e6-beb8-9e71128cae77',
                    new Address(
                        new Street('Wetstraat 1'),
                        new PostalCode('1000'),
                        new Locality('Brussel'),
                        Country::fromNative('BE')
                    )
                ),
                new ContactPointUpdated(
                    '9196cb78-4381-11e6-beb8-9e71128cae77',
                    new ContactPoint(['050/123'], ['test@test.be', 'test2@test.be'], ['http://www.google.be'])
                ),
            ],
            $this->eventStore->getEvents()
        );

        $this->assertEquals($expectedUuid, $organizerId);
    }

    /**
     * @test
     */
    public function it_sends_a_add_label_command()
    {
        $organizerId = 'organizerId';
        $label = new Label('foo');

        $expectedAddLabel = new AddLabel($organizerId, $label);

        $this->labelService->expects($this->once())
            ->method('createLabelAggregateIfNew')
            ->with(new LabelName('foo'));

        $this->commandBus->expects($this->once())
            ->method('dispatch')
            ->with($expectedAddLabel);

        $this->service->addLabel($organizerId, $label);
    }

    /**
     * @test
     */
    public function it_sends_a_remove_label_command()
    {
        $organizerId = 'organizerId';
        $label = new Label('foo');

        $expectedRemoveLabel = new RemoveLabel($organizerId, $label);

        $this->commandBus->expects($this->once())
            ->method('dispatch')
            ->with($expectedRemoveLabel);

        $this->service->removeLabel($organizerId, $label);
    }

    /**
     * @test
     */
    public function it_sends_a_delete_command()
    {
        $id = '1234';

        $expectedCommand = new DeleteOrganizer($id);
        $this->commandBus->expects($this->once())
            ->method('dispatch')
            ->with($expectedCommand);

        $this->service->delete($id);
    }
}
