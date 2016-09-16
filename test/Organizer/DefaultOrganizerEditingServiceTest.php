<?php

namespace CultuurNet\UDB3\Organizer;

use Broadway\CommandHandling\CommandBusInterface;
use Broadway\EventHandling\SimpleEventBus;
use Broadway\EventStore\InMemoryEventStore;
use Broadway\EventStore\TraceableEventStore;
use Broadway\Repository\RepositoryInterface;
use Broadway\UuidGenerator\UuidGeneratorInterface;
use CultuurNet\UDB3\Address;
use CultuurNet\UDB3\Organizer\Commands\DeleteOrganizer;
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
    public function it_can_create_an_organizer()
    {
        $this->eventStore->trace();

        $organizerId = $this->service->create(
            Url::fromNative('http://www.stuk.be'),
            new Title('Het Stuk'),
            [new Address('$street', '$postalCode', '$locality', '$country')],
            ['050/123'],
            ['test@test.be', 'test2@test.be'],
            ['http://www.google.be']
        );

        $expectedUuid = '9196cb78-4381-11e6-beb8-9e71128cae77';

        $this->assertEquals(
            [
                new OrganizerCreatedWithUniqueWebsite(
                    '9196cb78-4381-11e6-beb8-9e71128cae77',
                    Url::fromNative('http://www.stuk.be'),
                    new Title('Het Stuk'),
                    [new Address('$street', '$postalCode', '$locality', '$country')],
                    ['050/123'],
                    ['test@test.be', 'test2@test.be'],
                    ['http://www.google.be']
                )
            ],
            $this->eventStore->getEvents()
        );

        $this->assertEquals($expectedUuid, $organizerId);
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
