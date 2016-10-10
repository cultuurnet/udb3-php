<?php

namespace CultuurNet\UDB3\Organizer;

use Broadway\CommandHandling\CommandBusInterface;
use Broadway\Repository\RepositoryInterface;
use Broadway\UuidGenerator\UuidGeneratorInterface;
use CultuurNet\UDB3\Organizer\Commands\AddLabel;
use CultuurNet\UDB3\Organizer\Commands\DeleteOrganizer;
use CultuurNet\UDB3\Organizer\Commands\RemoveLabel;
use ValueObjects\Identity\UUID;

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
     * @var RepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
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
        $this->organizerRepository = $this->getMock(RepositoryInterface::class);

        $this->service = new DefaultOrganizerEditingService(
            $this->commandBus,
            $this->uuidGenerator,
            $this->organizerRepository
        );
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
