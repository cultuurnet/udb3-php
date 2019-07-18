<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Variations\ReadModel\Search;

use Broadway\Domain\DomainMessage;
use Broadway\Domain\Metadata;
use CultuurNet\UDB3\Offer\OfferType;
use CultuurNet\UDB3\Variations\Model\Events\OfferVariationCreated;
use CultuurNet\UDB3\Variations\Model\Events\OfferVariationDeleted;
use CultuurNet\UDB3\Variations\Model\Properties\Description;
use CultuurNet\UDB3\Variations\Model\Properties\Id;
use CultuurNet\UDB3\Variations\Model\Properties\OwnerId;
use CultuurNet\UDB3\Variations\Model\Properties\Purpose;
use CultuurNet\UDB3\Variations\Model\Properties\Url;
use PHPUnit\Framework\TestCase;

class ProjectorTest extends TestCase
{
    /**
     * @var RepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $repository;

    /**
     * @var Projector
     */
    private $projector;

    public function setUp()
    {
        $this->repository = $this->createMock(RepositoryInterface::class);
        $this->projector = new Projector($this->repository);
    }

    /**
     * @test
     */
    public function it_fills_the_search_index_on_creation_of_event_variations()
    {
        $id = new Id('147D80FA-B12E-46A9-B6B3-3A282AC9EBB1');
        $url = new Url('//io.uitdatabank.be/event/46D820EE-CDD8-4477-AADC-8AF9F421B04A');
        $ownerId = new OwnerId('5491E336-88B7-4106-9786-FB845BF9A0B8');
        $purpose = new Purpose('personal');

        $this->repository->expects($this->once())
            ->method('save')
            ->with(
                $id,
                $url,
                $ownerId,
                $purpose
            );

        $eventVariationCreated = new OfferVariationCreated(
            $id,
            $url,
            $ownerId,
            $purpose,
            new Description('my personal description of this event'),
            OfferType::EVENT()
        );

        $this->projector->handle(DomainMessage::recordNow(
            (string)$id,
            1,
            new Metadata(),
            $eventVariationCreated
        ));
    }

    /**
     * @test
     */
    public function it_removes_a_variation_from_the_search_index_on_deletion()
    {
        $id = new Id('147D80FA-B12E-46A9-B6B3-3A282AC9EBB1');

        $this->repository->expects($this->once())
            ->method('remove')
            ->with($id);

        $eventVariationDeleted = new OfferVariationDeleted($id);

        $this->projector->handle(DomainMessage::recordNow(
            (string)$id,
            2,
            new Metadata(),
            $eventVariationDeleted
        ));
    }
}
