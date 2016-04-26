<?php

namespace CultuurNet\UDB3\Offer;

use Broadway\Repository\AggregateNotFoundException;
use Broadway\Repository\RepositoryInterface;
use CultuurNet\UDB3\Event\Event;
use CultuurNet\UDB3\Label;
use CultuurNet\UDB3\Offer\Commands\AddLabelToMultiple;
use CultuurNet\UDB3\Offer\Commands\AddLabelToQuery;
use CultuurNet\UDB3\Place\Place;
use CultuurNet\UDB3\Search\ResultsGeneratorInterface;
use CultuurNet\UDB3\Variations\AggregateDeletedException;
use Psr\Log\LoggerInterface;
use ValueObjects\Web\Url;

class BulkLabelCommandHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ResultsGeneratorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultGenerator;

    /**
     * @var RepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $eventRepository;

    /**
     * @var RepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $placeRepository;

    /**
     * @var LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $logger;

    /**
     * @var BulkLabelCommandHandler
     */
    private $commandHandler;

    /**
     * @var string
     */
    private $query;

    /**
     * @var Label
     */
    private $label;

    /**
     * @var IriOfferIdentifier[]
     */
    private $offerIdentifiers;

    /**
     * @var Event|\PHPUnit_Framework_MockObject_MockObject
     */
    private $eventMock;

    /**
     * @var Place|\PHPUnit_Framework_MockObject_MockObject
     */
    private $placeMock;

    public function setUp()
    {
        $this->resultGenerator = $this->getMock(ResultsGeneratorInterface::class);
        $this->eventRepository = $this->getMock(RepositoryInterface::class);
        $this->placeRepository = $this->getMock(RepositoryInterface::class);

        $this->commandHandler = (new BulkLabelCommandHandler($this->resultGenerator))
            ->withRepository(OfferType::EVENT(), $this->eventRepository)
            ->withRepository(OfferType::PLACE(), $this->placeRepository);

        $this->logger = $this->getMock(LoggerInterface::class);
        $this->commandHandler->setLogger($this->logger);

        $this->query = 'city:leuven';
        $this->label = new Label('foo');

        $this->offerIdentifiers = [
            1 => new IriOfferIdentifier(
                Url::fromNative('http://du.de/event/1'),
                '1',
                OfferType::EVENT()
            ),
            2 => new IriOfferIdentifier(
                Url::fromNative('http://du.de/place/2'),
                '2',
                OfferType::PLACE()
            ),
        ];

        $this->eventMock = $this->getMock(Event::class);
        $this->placeMock = $this->getMock(Place::class);
    }

    /**
     * @test
     */
    public function it_can_label_all_offer_results_from_a_query()
    {
        $addLabelToQuery = new AddLabelToQuery(
            $this->query,
            $this->label
        );

        $this->resultGenerator->expects($this->once())
            ->method('search')
            ->with($this->query)
            ->willReturn($this->offerIdentifiers);

        $this->expectEventAndPlaceToBeLabelledWith($this->label);

        $this->commandHandler->handle($addLabelToQuery);
    }

    /**
     * @test
     */
    public function it_can_label_all_offers_from_a_selection()
    {
        $addLabelToMultiple = new AddLabelToMultiple(
            OfferIdentifierCollection::fromArray($this->offerIdentifiers),
            $this->label
        );

        $this->expectEventAndPlaceToBeLabelledWith($this->label);

        $this->commandHandler->handle($addLabelToMultiple);
    }

    /**
     * @test
     */
    public function it_throws_an_exception_when_no_repository_is_found_for_an_offer_type()
    {
        // Command handler without Place repository.
        $commandHandler = (new BulkLabelCommandHandler($this->resultGenerator))
            ->withRepository(OfferType::EVENT(), $this->eventRepository);

        $addLabelToMultiple = new AddLabelToMultiple(
            OfferIdentifierCollection::fromArray($this->offerIdentifiers),
            $this->label
        );

        $this->expectEventToBeLabelledWith($this->label);

        $this->setExpectedException(
            \LogicException::class,
            "Found no repository for type Place."
        );

        $commandHandler->handle($addLabelToMultiple);
    }

    /**
     * @test
     * @dataProvider aggregateExceptionDataProvider
     *
     * @param \Exception $exception
     * @param string $errorMessage
     */
    public function it_logs_an_error_when_an_entity_is_not_found_and_continues_labelling(
        \Exception $exception,
        $errorMessage
    ) {
        // Make sure we log any attempts to label non-existing / removed events.
        $this->eventRepository->expects($this->once())
            ->method('load')
            ->with(1)
            ->willThrowException($exception);

        $this->logger->expects($this->once())
            ->method('error')
            ->with(
                $errorMessage,
                [
                    'id' => '1',
                    'type' => 'Event',
                    'command' => 'CultuurNet\UDB3\Offer\Commands\AddLabelToMultiple',
                ]
            );

        // Make sure the place is still labelled.
        $this->expectPlaceToBeLabelledWith($this->label);

        $this->commandHandler->handle(
            new AddLabelToMultiple(
                OfferIdentifierCollection::fromArray($this->offerIdentifiers),
                $this->label
            )
        );
    }

    /**
     * @return array
     */
    public function aggregateExceptionDataProvider()
    {
        return [
            [
                AggregateNotFoundException::create(1),
                'bulk_label_command_entity_not_found',
            ],
            [
                AggregateDeletedException::create(1),
                'bulk_label_command_entity_deleted',
            ],
        ];
    }

    /**
     * @param Label $label
     */
    private function expectEventAndPlaceToBeLabelledWith(Label $label)
    {
        $this->expectEventToBeLabelledWith($label);
        $this->expectPlaceToBeLabelledWith($label);
    }

    /**
     * @param Label $label
     */
    private function expectEventToBeLabelledWith(Label $label)
    {
        $this->eventRepository->expects($this->once())
            ->method('load')
            ->with(1)
            ->willReturn($this->eventMock);

        $this->eventMock->expects($this->once())
            ->method('addLabel')
            ->with($label);

        $this->eventRepository->expects($this->once())
            ->method('save')
            ->with($this->eventMock);
    }

    /**
     * @param Label $label
     */
    private function expectPlaceToBeLabelledWith(Label $label)
    {
        $this->placeRepository->expects($this->once())
            ->method('load')
            ->with(2)
            ->willReturn($this->placeMock);

        $this->placeMock->expects($this->once())
            ->method('addLabel')
            ->with($label);

        $this->placeRepository->expects($this->once())
            ->method('save')
            ->with($this->placeMock);
    }
}
