<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Variations\Command;

use Broadway\CommandHandling\Testing\CommandHandlerScenarioTestCase;
use Broadway\EventHandling\EventBusInterface;
use Broadway\EventStore\EventStoreInterface;
use Broadway\UuidGenerator\UuidGeneratorInterface;
use CultuurNet\UDB3\Variations\DefaultEventVariationService;
use CultuurNet\UDB3\Variations\EventVariationRepository;
use CultuurNet\UDB3\Variations\Model\Events\DescriptionEdited;
use CultuurNet\UDB3\Variations\Model\Events\EventVariationCreated;
use CultuurNet\UDB3\Variations\Model\Properties\Description;
use CultuurNet\UDB3\Variations\Model\Properties\Id;
use CultuurNet\UDB3\Variations\Model\Properties\OwnerId;
use CultuurNet\UDB3\Variations\Model\Properties\Purpose;
use CultuurNet\UDB3\Variations\Model\Properties\Url;
use ValueObjects\Identity\UUID;

class EventVariationCommandHandlerTest extends CommandHandlerScenarioTestCase
{
    /**
     * @var UuidGeneratorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $generator;

    public function setUp()
    {
        $this->generator = $this->getMock(UuidGeneratorInterface::class);
        parent::setUp();
    }

    /**
     * @inheritdoc
     */
    protected function createCommandHandler(EventStoreInterface $eventStore, EventBusInterface $eventBus)
    {
        $eventVariationService = new DefaultEventVariationService(
            new EventVariationRepository($eventStore, $eventBus),
            $this->generator
        );

        return new EventVariationCommandHandler($eventVariationService);
    }

    /**
     * @test
     */
    public function it_can_create_a_new_variation()
    {
        $id = UUID::generateAsString();

        $eventUrl = new Url('//beta.uitdatabank.be/event/5abf2278-a916-4dee-a198-94b57db66e98');
        $ownerId = new OwnerId('xyz');
        $purpose = new Purpose('personal');
        $description = new Description('my own description');

        $this->generator->expects($this->once())
            ->method('generate')
            ->willReturn($id);

        $this->scenario
            ->withAggregateId($id)
            ->when(new CreateEventVariation(
                $eventUrl,
                $ownerId,
                $purpose,
                $description
            ))
            ->then(
                [
                    new EventVariationCreated(
                        new Id($id),
                        $eventUrl,
                        $ownerId,
                        $purpose,
                        $description
                    )
                ]
            );
    }

    /**
     * TODO: not sure why this is breaking, fix and add test annotation
     */
    public function it_can_edit_a_description()
    {
        $id = new Id(UUID::generateAsString());
        $eventUrl = new Url('//beta.uitdatabank.be/event/5abf2278-a916-4dee-a198-94b57db66e98');
        $ownerId = new OwnerId('xyz');
        $purpose = new Purpose('personal');
        $description = new Description('my own description');

        $newDescription = new Description('A new description.');

        $this->scenario
            ->withAggregateId((string) $id)
            ->given([new EventVariationCreated(
                $id,
                $eventUrl,
                $ownerId,
                $purpose,
                $description
            )])
            ->when(new EditDescription($id, $newDescription))
            ->then([new DescriptionEdited($id, $newDescription)]);
    }
}
