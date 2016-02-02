<?php

namespace CultuurNet\UDB3\Offer;

use Broadway\CommandHandling\CommandBusInterface;
use Broadway\UuidGenerator\UuidGeneratorInterface;
use Broadway\Repository\RepositoryInterface;
use CultuurNet\UDB3\Event\ReadModel\DocumentRepositoryInterface;
use CultuurNet\UDB3\Label;
use CultuurNet\UDB3\Offer\Commands\AbstractAddLabel;
use CultuurNet\UDB3\Offer\Commands\AbstractDeleteLabel;
use CultuurNet\UDB3\Offer\Commands\OfferCommandFactoryInterface;

class DefaultOfferEditingServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CommandBusInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $commandBus;

    /**
     * @var UuidGeneratorInterface
     */
    private $uuidGenerator;

    /**
     * @var DocumentRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $offerRepository;

    /**
     * @var OfferCommandFactoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $commandFactory;

    /**
     * @var DefaultOfferEditingService
     */
    private $offerEditingService;

    /**
     * @var AbstractAddLabel
     */
    private $addLabelCommand;

    /**
     * @var AbstractDeleteLabel
     */
    private $deleteLabelCommand;

    public function setUp()
    {
        $this->commandBus = $this->getMock(CommandBusInterface::class);
        $this->uuidGenerator = $this->getMock(UuidGeneratorInterface::class);
        $this->offerRepository = $this->getMock(DocumentRepositoryInterface::class);
        $this->commandFactory = $this->getMock(OfferCommandFactoryInterface::class);

        $this->addLabelCommand = $this->getMockForAbstractClass(
            AbstractAddLabel::class,
            array('foo', new Label('label1'))
        );

        $this->deleteLabelCommand = $this->getMockForAbstractClass(
            AbstractDeleteLabel::class,
            array('foo', new Label('label1'))
        );

        $this->offerEditingService = new DefaultOfferEditingService(
            $this->commandBus,
            $this->uuidGenerator,
            $this->offerRepository,
            $this->commandFactory
        );
    }

    /**
     * @test
     */
    public function it_can_add_a_label()
    {
        $this->offerRepository->expects($this->once())
            ->method('get')
            ->with('foo');

        $this->commandFactory->expects($this->once())
            ->method('createAddLabelCommand')
            ->with('foo', new Label('label1'))
            ->willReturn($this->addLabelCommand);

        $this->commandBus->expects($this->once())
            ->method('dispatch');

        $this->offerEditingService->addLabel('foo', new Label('label1'));
    }

    /**
     * @test
     */
    public function it_can_delete_a_label()
    {
        $this->offerRepository->expects($this->once())
            ->method('get')
            ->with('foo');

        $this->commandFactory->expects($this->once())
            ->method('createDeleteLabelCommand')
            ->with('foo', new Label('label1'))
            ->willReturn($this->addLabelCommand);

        $this->commandBus->expects($this->once())
            ->method('dispatch');

        $this->offerEditingService->deleteLabel('foo', new Label('label1'));
    }
}
