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
use CultuurNet\UDB3\Organizer\Commands\AddLabel;
use CultuurNet\UDB3\Address\Address;
use CultuurNet\UDB3\ContactPoint;
use CultuurNet\UDB3\Organizer\Commands\DeleteOrganizer;
use CultuurNet\UDB3\Organizer\Commands\RemoveLabel;
use CultuurNet\UDB3\Organizer\Events\AddressUpdated;
use CultuurNet\UDB3\Organizer\Events\ContactPointUpdated;
use ValueObjects\Geography\Country;
use ValueObjects\Identity\UUID;
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
     * @var DefaultOrganizerEditingService
     */
    private $service;

    public function setUp()
    {
        $this->commandBus = $this->getMock(CommandBusInterface::class);
        $this->uuidGenerator = $this->getMock(UuidGeneratorInterface::class);

        $this->eventStore = new TraceableEventStore(new InMemoryEventStore());

        $this->organizerRepository = new OrganizerRepository(
            $this->eventStore,
            new SimpleEventBus
        );

        $this->uuidGenerator->method('generate')
            ->willReturn('9196cb78-4381-11e6-beb8-9e71128cae77');

        $this->service = new DefaultOrganizerEditingService(
            $this->commandBus,
            $this->uuidGenerator,
            $this->organizerRepository
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
        $labelId = new UUID();

        $expectedAddLabel = new AddLabel($organizerId, $labelId);

        $this->commandBus->expects($this->once())
            ->method('dispatch')
            ->with($expectedAddLabel);

        $this->service->addLabel($organizerId, $labelId);
    }

    /**
     * @test
     */
    public function it_sends_a_remove_label_command()
    {
        $organizerId = 'organizerId';
        $labelId = new UUID();

        $expectedRemoveLabel = new RemoveLabel($organizerId, $labelId);

        $this->commandBus->expects($this->once())
            ->method('dispatch')
            ->with($expectedRemoveLabel);

        $this->service->removeLabel($organizerId, $labelId);
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
