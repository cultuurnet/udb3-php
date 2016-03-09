<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Variations;

use Broadway\EventHandling\SimpleEventBus;
use Broadway\EventStore\InMemoryEventStore;
use Broadway\EventStore\TraceableEventStore;
use CultuurNet\UDB3\Offer\OfferType;
use CultuurNet\UDB3\Variations\Model\OfferVariation;
use CultuurNet\UDB3\Variations\Model\Properties\Description;
use CultuurNet\UDB3\Variations\Model\Properties\Id;
use CultuurNet\UDB3\Variations\Model\Properties\OwnerId;
use CultuurNet\UDB3\Variations\Model\Properties\Purpose;
use CultuurNet\UDB3\Variations\Model\Properties\Url;
use PHPUnit_Framework_TestCase;

class OfferVariationRepositoryTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var OfferVariationRepository
     */
    private $repository;

    public function setUp()
    {
        $this->repository = new OfferVariationRepository(
            new TraceableEventStore(
                new InMemoryEventStore()
            ),
            new SimpleEventBus()
        );
    }

    /**
     * @test
     */
    public function it_refuses_to_load_deleted_events()
    {
        $id = new Id('29d6d973-ca78-4561-b593-631502c74a8c');
        $variation = OfferVariation::create(
            $id,
            new Url('//beta.uitdatabank.be/event/xyz'),
            new OwnerId('b7159c3d-8ba2-499c-b4ca-01767a95625d'),
            new Purpose('personal'),
            new Description('my custom description'),
            OfferType::EVENT()
        );

        $variation->markDeleted();

        $this->repository->save($variation);

        $this->setExpectedException(
            AggregateDeletedException::class,
            "Aggregate with id '{$id}' was deleted"
        );
        $this->repository->load((string)$id);
    }
}
