<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Variations\ReadModel\Search;

use Broadway\Domain\DomainMessage;
use Broadway\Domain\Metadata;
use CultuurNet\UDB3\Variations\Model\Events\EventVariationCreated;
use CultuurNet\UDB3\Variations\Model\Events\EventVariationDeleted;
use CultuurNet\UDB3\Variations\Model\Properties\Description;
use CultuurNet\UDB3\Variations\Model\Properties\Id;
use CultuurNet\UDB3\Variations\Model\Properties\OwnerId;
use CultuurNet\UDB3\Variations\Model\Properties\Purpose;
use CultuurNet\UDB3\Variations\Model\Properties\Url;
use PHPUnit_Framework_TestCase;

class ProjectorTest extends PHPUnit_Framework_TestCase
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
        $this->repository = $this->getMock(RepositoryInterface::class);
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

        $eventVariationCreated = new EventVariationCreated(
            $id,
            $url,
            $ownerId,
            $purpose,
            new Description('my personal description of this event')
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

        $eventVariationDeleted = new EventVariationDeleted($id);

        $this->projector->handle(DomainMessage::recordNow(
            (string)$id,
            2,
            new Metadata(),
            $eventVariationDeleted
        ));
    }
}
