<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\UDB2\Place;

use Broadway\Repository\AggregateNotFoundException;
use Broadway\Repository\RepositoryInterface;
use CultuurNet\UDB3\Place\Place;
use CultuurNet\UDB3\UDB2\EntryAPIImprovedFactoryInterface;

class PlaceRepositoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var PlaceRepository
     */
    private $placeRepository;

    /**
     * @var RepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $innerRepository;

    /**
     * @var PlaceImporterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $placeImporter;

    public function setUp()
    {
        $this->innerRepository = $this->getMock(RepositoryInterface::class);
        $this->placeImporter = $this->getMock(
            PlaceImporterInterface::class
        );
        $this->placeRepository = new PlaceRepository(
            $this->innerRepository,
            $this->getMock(EntryAPIImprovedFactoryInterface::class),
            $this->placeImporter
        );
    }

    /**
     * @test
     */
    public function it_calls_the_importer_if_the_place_is_missing_in_the_decorated_repository()
    {
        $id = 'foo';

        $this->innerRepository->expects($this->once())
            ->method('load')
            ->with($id)
            ->willThrowException(
                new AggregateNotFoundException()
            );

        $this->placeImporter->expects($this->once())
            ->method('createPlaceFromUDB2')
            ->with($id)
            ->willReturn(new Place());

        $this->placeRepository->load($id);
    }

    /**
     * @test
     */
    public function it_reports_an_exception_if_the_importer_does_not_succeed()
    {
        $id = 'foo';

        $this->innerRepository->expects($this->once())
            ->method('load')
            ->with($id)
            ->willThrowException(
                new AggregateNotFoundException()
            );

        $this->placeImporter->expects($this->once())
            ->method('createPlaceFromUDB2')
            ->with($id);

        $this->setExpectedException(AggregateNotFoundException::class);

        $this->placeRepository->load($id);
    }
}
