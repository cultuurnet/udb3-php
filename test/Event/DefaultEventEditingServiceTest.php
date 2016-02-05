<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Event;

use Broadway\CommandHandling\CommandBusInterface;
use CultuurNet\UDB3\Event\ReadModel\DocumentGoneException;
use CultuurNet\UDB3\Event\ReadModel\DocumentRepositoryInterface;
use CultuurNet\UDB3\EventNotFoundException;
use CultuurNet\UDB3\EventServiceInterface;
use CultuurNet\UDB3\Label;
use CultuurNet\UDB3\Language;
use Broadway\UuidGenerator\UuidGeneratorInterface;
use Broadway\Repository\RepositoryInterface;
use CultuurNet\UDB3\Offer\Commands\OfferCommandFactoryInterface;
use CultuurNet\UDB3\PlaceService;
use ValueObjects\String\String;

class DefaultEventEditingServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DefaultEventEditingService
     */
    protected $eventEditingService;

    /**
     * @var EventServiceInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $eventService;

    /**
     * @var CommandBusInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $commandBus;

    /**
     * @var UuidGeneratorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $uuidGenerator;

    /**
     * @var OfferCommandFactoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $commandFactory;

    /**
     * @var DocumentRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $repository;

    public function setUp()
    {
        $this->eventService = $this->getMock(EventServiceInterface::class);

        $this->commandBus = $this->getMock(CommandBusInterface::class);

        $this->uuidGenerator = $this->getMock(
            UuidGeneratorInterface::class
        );

        $this->commandFactory = $this->getMock(OfferCommandFactoryInterface::class);

        /** @var DocumentRepositoryInterface $repository */
        $this->repository = $this->getMock(DocumentRepositoryInterface::class);
        /** @var PlaceService $placeService */
        $placeService = $this->getMock(
            PlaceService::class,
            array(),
            array(),
            '',
            false
        );
        $this->eventEditingService = new DefaultEventEditingService(
            $this->eventService,
            $this->commandBus,
            $this->uuidGenerator,
            $this->repository,
            $placeService,
            $this->commandFactory
        );
    }

    /**
     * @test
     */
    public function it_refuses_to_translate_title_of_unknown_event()
    {
        $id = 'some-unknown-id';

        $this->setExpectedException(DocumentGoneException::class);

        $this->setUpEventNotFound($id);

        $this->eventEditingService->translateTitle(
            $id,
            new Language('nl'),
            new String('new title')
        );
    }

    /**
     * @test
     */
    public function it_refuses_to_translate_description_of_unknown_event()
    {
        $id = 'some-unknown-id';

        $this->setExpectedException(DocumentGoneException::class);

        $this->setUpEventNotFound($id);

        $this->eventEditingService->translateDescription(
            $id,
            new Language('nl'),
            new String('new description')
        );
    }

    /**
     * @test
     */
    public function it_refuses_to_label_an_unknown_event()
    {
        $id = 'some-unknown-id';

        $this->setExpectedException(DocumentGoneException::class);

        $this->setUpEventNotFound($id);

        $this->eventEditingService->addLabel($id, new Label('foo'));
    }

    /**
     * @test
     */
    public function it_refuses_to_remove_a_label_from_an_unknown_event()
    {
        $id = 'some-unknown-id';

        $this->setExpectedException(DocumentGoneException::class);

        $this->setUpEventNotFound($id);

        $this->eventEditingService->deleteLabel($id, new Label('foo'));
    }

    private function setUpEventNotFound($id)
    {
        $this->repository->expects($this->once())
            ->method('get')
            ->with($id)
            ->willThrowException(new DocumentGoneException());
    }
}
