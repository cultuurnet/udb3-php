<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Variations;

use Broadway\EventHandling\SimpleEventBus;
use Broadway\EventHandling\TraceableEventBus;
use Broadway\EventStore\InMemoryEventStore;
use Broadway\EventStore\TraceableEventStore;
use Broadway\UuidGenerator\UuidGeneratorInterface;
use CultuurNet\UDB3\Offer\IriOfferIdentifier;
use CultuurNet\UDB3\Offer\OfferType;
use CultuurNet\UDB3\Variations\Model\Events\OfferVariationCreated;
use CultuurNet\UDB3\Variations\Model\Events\OfferVariationDeleted;
use CultuurNet\UDB3\Variations\Model\Properties\Description;
use CultuurNet\UDB3\Variations\Model\Properties\Id;
use CultuurNet\UDB3\Variations\Model\Properties\OwnerId;
use CultuurNet\UDB3\Variations\Model\Properties\Purpose;
use CultuurNet\UDB3\Variations\Model\Properties\Url;

class DefaultOfferVariationServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var TraceableEventStore
     */
    private $eventStore;

    /**
     * @var TraceableEventBus
     */
    private $eventBus;

    /**
     * @var UuidGeneratorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $uuidGenerator;

    /**
     * @var DefaultOfferVariationService
     */
    private $variationService;

    public function setUp()
    {
        $this->eventStore = new TraceableEventStore(
            new InMemoryEventStore()
        );

        $this->eventBus = new TraceableEventBus(
            new SimpleEventBus()
        );

        $this->uuidGenerator = $this->getMock(
            UuidGeneratorInterface::class
        );

        $repository = new EventVariationRepository(
            $this->eventStore,
            $this->eventBus
        );

        $this->variationService = new DefaultOfferVariationService(
            $repository,
            $this->uuidGenerator
        );
    }

    /**
     * @test
     */
    public function it_creates_new_variations()
    {
        $this->eventStore->trace();

        $this->uuidGenerator->expects($this->once())
            ->method('generate')
            ->willReturn('19910c56-db76-4cf6-bc28-b25a7270de2e');

        $variation = $this->variationService->createEventVariation(
            new IriOfferIdentifier(
                '//beta.uitdatabank.be/event/235',
                '235',
                OfferType::EVENT()
            ),
            new OwnerId('xyz'),
            new Purpose('personal'),
            new Description('my personal description')
        );

        $this->assertEquals(
            '19910c56-db76-4cf6-bc28-b25a7270de2e',
            $variation->getAggregateRootId()
        );

        $this->assertEquals(
            [
                new OfferVariationCreated(
                    new Id('19910c56-db76-4cf6-bc28-b25a7270de2e'),
                    new Url('//beta.uitdatabank.be/event/235'),
                    new OwnerId('xyz'),
                    new Purpose('personal'),
                    new Description('my personal description'),
                    OfferType::EVENT()
                )
            ],
            $this->eventStore->getEvents()
        );
    }

    /**
     * @test
     */
    public function it_can_delete_variations()
    {
        $this->eventStore->trace();

        $this->uuidGenerator->expects($this->once())
            ->method('generate')
            ->willReturn('29910c56-db76-4cf6-bc28-b25a7270de2e');

        $this->variationService->createEventVariation(
            new IriOfferIdentifier(
                '//beta.uitdatabank.be/event/235',
                '235',
                OfferType::EVENT()
            ),
            new OwnerId('xyz'),
            new Purpose('personal'),
            new Description('my personal description')
        );

        $variationId = new Id('29910c56-db76-4cf6-bc28-b25a7270de2e');
        $this->variationService->deleteEventVariation($variationId);

        // get the events and remove the creation event before asserting
        $events = $this->eventStore->getEvents();
        array_shift($events);

        $this->assertEquals(
            [
                new OfferVariationDeleted($variationId)
            ],
            $events
        );
    }
}
