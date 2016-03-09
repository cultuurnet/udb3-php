<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Variations\Command;

use Broadway\CommandHandling\Testing\CommandHandlerScenarioTestCase;
use Broadway\EventHandling\EventBusInterface;
use Broadway\EventStore\EventStoreInterface;
use Broadway\UuidGenerator\UuidGeneratorInterface;
use CultuurNet\UDB3\Offer\IriOfferIdentifier;
use CultuurNet\UDB3\Offer\OfferType;
use CultuurNet\UDB3\Variations\DefaultOfferVariationService;
use CultuurNet\UDB3\Variations\OfferVariationRepository;
use CultuurNet\UDB3\Variations\Model\Events\DescriptionEdited;
use CultuurNet\UDB3\Variations\Model\Events\OfferVariationCreated;
use CultuurNet\UDB3\Variations\Model\Events\OfferVariationDeleted;
use CultuurNet\UDB3\Variations\Model\Properties\Description;
use CultuurNet\UDB3\Variations\Model\Properties\Id;
use CultuurNet\UDB3\Variations\Model\Properties\OwnerId;
use CultuurNet\UDB3\Variations\Model\Properties\Purpose;
use CultuurNet\UDB3\Variations\Model\Properties\Url;
use Psr\Log\LoggerInterface;
use ValueObjects\Identity\UUID;

class OfferVariationCommandHandlerTest extends CommandHandlerScenarioTestCase
{
    /**
     * @var UuidGeneratorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $generator;

    /**
     * @var LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $logger;

    public function setUp()
    {
        $this->generator = $this->getMock(UuidGeneratorInterface::class);
        $this->logger = $this->getMock(LoggerInterface::class);
        parent::setUp();
    }

    /**
     * @inheritdoc
     */
    protected function createCommandHandler(EventStoreInterface $eventStore, EventBusInterface $eventBus)
    {
        $eventVariationService = new DefaultOfferVariationService(
            new OfferVariationRepository($eventStore, $eventBus),
            $this->generator
        );

        $commandHandler = new OfferVariationCommandHandler($eventVariationService);
        $commandHandler->setLogger($this->logger);

        return $commandHandler;
    }

    /**
     * @test
     */
    public function it_can_create_a_new_variation()
    {
        $id = UUID::generateAsString();

        $identifier = new IriOfferIdentifier(
            '//beta.uitdatabank.be/event/5abf2278-a916-4dee-a198-94b57db66e98',
            '5abf2278-a916-4dee-a198-94b57db66e98',
            OfferType::EVENT()
        );
        $ownerId = new OwnerId('xyz');
        $purpose = new Purpose('personal');
        $description = new Description('my own description');

        $this->generator->expects($this->once())
            ->method('generate')
            ->willReturn($id);

        $this->logger->expects($this->once())
            ->method('info')
            ->with('job_info', ['event_variation_id' => $id]);

        $this->scenario
            ->withAggregateId($id)
            ->when(new CreateOfferVariation(
                $identifier,
                $ownerId,
                $purpose,
                $description
            ))
            ->then(
                [
                    new OfferVariationCreated(
                        new Id($id),
                        new Url($identifier->getIri()),
                        $ownerId,
                        $purpose,
                        $description,
                        $identifier->getType()
                    )
                ]
            );
    }

    /**
     * @test
     */
    public function it_can_edit_a_description()
    {
        $creationEvent = $this->getExampleVariationCreatedEvent();
        $id = $creationEvent->getId();

        $newDescription = new Description('A new description.');

        $this->logger->expects($this->once())
            ->method('info')
            ->with('job_info', ['event_variation_id' => $id]);

        $this->scenario
            ->withAggregateId((string) $id)
            ->given([$creationEvent])
            ->when(new EditDescription($id, $newDescription))
            ->then([new DescriptionEdited($id, $newDescription)]);
    }

    /**
     * @test
     */
    public function it_can_delete_a_variation()
    {
        $creationEvent = $this->getExampleVariationCreatedEvent();
        $id = $creationEvent->getId();

        $this->logger->expects($this->once())
            ->method('info')
            ->with('job_info', ['event_variation_id' => $id]);

        $this->scenario
            ->withAggregateId((string) $id)
            ->given([$creationEvent])
            ->when(new DeleteOfferVariation($id))
            ->then([new OfferVariationDeleted($id)]);
    }

    /**
     * @return OfferVariationCreated
     */
    private function getExampleVariationCreatedEvent()
    {
        return new OfferVariationCreated(
            $id = new Id(UUID::generateAsString()),
            $eventUrl = new Url('//beta.uitdatabank.be/event/5abf2278-a916-4dee-a198-94b57db66e98'),
            $ownerId = new OwnerId('xyz'),
            $purpose = new Purpose('personal'),
            $description = new Description('my own description'),
            OfferType::EVENT()
        );
    }
}
