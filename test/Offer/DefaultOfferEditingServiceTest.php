<?php

namespace CultuurNet\UDB3\Offer;

use Broadway\CommandHandling\CommandBusInterface;
use Broadway\UuidGenerator\UuidGeneratorInterface;
use CultuurNet\UDB3\Description;
use CultuurNet\UDB3\Event\ReadModel\DocumentRepositoryInterface;
use CultuurNet\UDB3\Label;
use CultuurNet\UDB3\Label\LabelServiceInterface;
use CultuurNet\UDB3\Label\ValueObjects\LabelName;
use CultuurNet\UDB3\Language;
use CultuurNet\UDB3\Offer\Commands\AbstractAddLabel;
use CultuurNet\UDB3\Offer\Commands\AbstractRemoveLabel;
use CultuurNet\UDB3\Offer\Commands\AbstractUpdateTitle;
use CultuurNet\UDB3\Offer\Commands\AbstractUpdatePriceInfo;
use CultuurNet\UDB3\Offer\Commands\OfferCommandFactoryInterface;
use CultuurNet\UDB3\Offer\Item\Commands\UpdateDescription;
use CultuurNet\UDB3\Offer\Item\Commands\UpdateTitle;
use CultuurNet\UDB3\PriceInfo\BasePrice;
use CultuurNet\UDB3\PriceInfo\Price;
use CultuurNet\UDB3\PriceInfo\PriceInfo;
use ValueObjects\Money\Currency;
use ValueObjects\StringLiteral\StringLiteral;

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
     * @var LabelServiceInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $labelService;

    /**
     * @var DefaultOfferEditingService
     */
    private $offerEditingService;

    /**
     * @var AbstractAddLabel
     */
    private $addLabelCommand;

    /**
     * @var AbstractRemoveLabel
     */
    private $removeLabelCommand;

    /**
     * @var string
     */
    private $expectedCommandId;

    /**
     * @var AbstractUpdateTitle
     */
    private $translateTitleCommand;

    public function setUp()
    {
        $this->commandBus = $this->createMock(CommandBusInterface::class);
        $this->uuidGenerator = $this->createMock(UuidGeneratorInterface::class);
        $this->offerRepository = $this->createMock(DocumentRepositoryInterface::class);
        $this->commandFactory = $this->createMock(OfferCommandFactoryInterface::class);
        $this->labelService = $this->createMock(LabelServiceInterface::class);

        $this->addLabelCommand = $this->getMockForAbstractClass(
            AbstractAddLabel::class,
            array('foo', new Label('label1'))
        );

        $this->removeLabelCommand = $this->getMockForAbstractClass(
            AbstractRemoveLabel::class,
            array('foo', new Label('label1'))
        );

        $this->translateTitleCommand = $this->getMockForAbstractClass(
            AbstractUpdateTitle::class,
            array('foo', new Language('en'), new StringLiteral('English title'))
        );

        $this->offerEditingService = new DefaultOfferEditingService(
            $this->commandBus,
            $this->uuidGenerator,
            $this->offerRepository,
            $this->commandFactory,
            $this->labelService
        );

        $this->expectedCommandId = '123456';
    }

    /**
     * @test
     */
    public function it_can_add_a_label()
    {
        $this->offerRepository->expects($this->once())
            ->method('get')
            ->with('foo');

        $this->labelService->expects($this->once())
            ->method('createLabelAggregateIfNew')
            ->with(new LabelName('label1'));

        $this->commandFactory->expects($this->once())
            ->method('createAddLabelCommand')
            ->with('foo', new Label('label1'))
            ->willReturn($this->addLabelCommand);

        $this->commandBus->expects($this->once())
            ->method('dispatch')
            ->willReturn($this->expectedCommandId);

        $commandId = $this->offerEditingService->addLabel('foo', new Label('label1'));

        $this->assertEquals($this->expectedCommandId, $commandId);
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
            ->method('createRemoveLabelCommand')
            ->with('foo', new Label('label1'))
            ->willReturn($this->addLabelCommand);

        $this->commandBus->expects($this->once())
            ->method('dispatch')
            ->willReturn($this->expectedCommandId);

        $commandId = $this->offerEditingService->removeLabel('foo', new Label('label1'));

        $this->assertEquals($this->expectedCommandId, $commandId);
    }

    /**
     * @test
     */
    public function it_can_update_a_title_in_a_given_language()
    {
        $this->offerRepository->expects($this->once())
            ->method('get')
            ->with('foo');

        $this->commandFactory->expects($this->once())
            ->method('createUpdateTitleCommand')
            ->with('foo', new Language('en'), new StringLiteral('English title'))
            ->willReturn(new UpdateTitle('foo', new Language('en'), new StringLiteral('English title')));

        $this->commandBus->expects($this->once())
            ->method('dispatch')
            ->willReturn($this->expectedCommandId);

        $commandId = $this->offerEditingService->updateTitle(
            'foo',
            new Language('en'),
            new StringLiteral('English title')
        );

        $this->assertEquals($this->expectedCommandId, $commandId);
    }

    /**
     * @test
     */
    public function it_can_update_the_description_in_a_given_language()
    {
        $this->offerRepository->expects($this->once())
            ->method('get')
            ->with('foo');

        $this->commandFactory->expects($this->once())
            ->method('createUpdateDescriptionCommand')
            ->with('foo', new Language('fr'), new Description('La description'))
            ->willReturn(new UpdateDescription('foo', new Language('fr'), new Description('La description')));

        $this->commandBus->expects($this->once())
            ->method('dispatch')
            ->willReturn($this->expectedCommandId);

        $commandId = $this->offerEditingService->updateDescription(
            'foo',
            new Language('fr'),
            new Description('La description')
        );

        $this->assertEquals($this->expectedCommandId, $commandId);
    }

    /**
     * @test
     */
    public function it_can_update_price_info()
    {
        $aggregateId = '940ce4d1-740b-43d2-a1a6-85be04a3eb30';
        $expectedCommandId = 'f42802e4-c1f1-4aa6-9909-a08cfc66f355';

        $priceInfo = new PriceInfo(
            new BasePrice(
                Price::fromFloat(10.5),
                Currency::fromNative('EUR')
            )
        );

        $updatePriceInfoCommand = $this->getMockForAbstractClass(
            AbstractUpdatePriceInfo::class,
            array($aggregateId, $priceInfo)
        );

        $this->commandFactory->expects($this->once())
            ->method('createUpdatePriceInfoCommand')
            ->with($aggregateId, $priceInfo)
            ->willReturn($updatePriceInfoCommand);

        $this->commandBus->expects($this->once())
            ->method('dispatch')
            ->with($updatePriceInfoCommand)
            ->willReturn($expectedCommandId);

        $commandId = $this->offerEditingService->updatePriceInfo(
            $aggregateId,
            $priceInfo
        );

        $this->assertEquals($expectedCommandId, $commandId);
    }
}
