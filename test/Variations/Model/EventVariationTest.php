<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Variations\Model;

use Broadway\Domain\DomainMessage;
use Broadway\EventSourcing\EventSourcedAggregateRoot;
use CultuurNet\UDB3\Variations\AggregateDeletedException;
use CultuurNet\UDB3\Variations\Model\Events\EventVariationCreated;
use CultuurNet\UDB3\Variations\Model\Properties\Description;
use CultuurNet\UDB3\Variations\Model\Properties\Id;
use CultuurNet\UDB3\Variations\Model\Properties\OwnerId;
use CultuurNet\UDB3\Variations\Model\Properties\Purpose;
use CultuurNet\UDB3\Variations\Model\Properties\Url;

class EventVariationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_can_be_created_with_a_factory_method()
    {
        $eventVariation = EventVariation::create(
            new Id('29d6d973-ca78-4561-b593-631502c74a8c'),
            new Url('//beta.uitdatabank.be/event/xyz'),
            new OwnerId('b7159c3d-8ba2-499c-b4ca-01767a95625d'),
            new Purpose('personal'),
            new Description('my custom description')
        );

        $this->assertUncommittedEventsEquals(
            [
                new EventVariationCreated(
                    new Id('29d6d973-ca78-4561-b593-631502c74a8c'),
                    new Url('//beta.uitdatabank.be/event/xyz'),
                    new OwnerId('b7159c3d-8ba2-499c-b4ca-01767a95625d'),
                    new Purpose('personal'),
                    new Description('my custom description')
                )
            ],
            $eventVariation
        );

        $this->assertSame(
            '29d6d973-ca78-4561-b593-631502c74a8c',
            $eventVariation->getAggregateRootId()
        );
    }

    /**
     * @test
     */
    public function its_description_can_be_edited()
    {
        $eventVariation = EventVariation::create(
            new Id('29d6d973-ca78-4561-b593-631502c74a8c'),
            new Url('//beta.uitdatabank.be/event/xyz'),
            new OwnerId('b7159c3d-8ba2-499c-b4ca-01767a95625d'),
            new Purpose('personal'),
            new Description('my custom description')
        );

        $description = new Description('An edited description');

        $eventVariation->editDescription($description);

        $this->assertEquals($description, $eventVariation->getDescription());
    }

    /**
     * @test
     */
    public function it_can_be_deleted()
    {
        $eventVariation = EventVariation::create(
            new Id('29d6d973-ca78-4561-b593-631502c74a8c'),
            new Url('//beta.uitdatabank.be/event/xyz'),
            new OwnerId('b7159c3d-8ba2-499c-b4ca-01767a95625d'),
            new Purpose('personal'),
            new Description('my custom description')
        );

        $eventVariation->markDeleted();
        $this->assertTrue($eventVariation->isDeleted());

        $this->setExpectedException(AggregateDeletedException::class);
        $eventVariation->markDeleted();
    }

    private function assertUncommittedEventsEquals(
        array $expected,
        EventSourcedAggregateRoot $aggregateRoot
    ) {
        $this->assertEquals(
            $expected,
            array_map(
                function (DomainMessage $message) {
                    return $message->getPayload();
                },
                iterator_to_array($aggregateRoot->getUncommittedEvents())
            )
        );
    }
}
