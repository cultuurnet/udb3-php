<?php

namespace CultuurNet\UDB3\Place;

use Broadway\Repository\RepositoryInterface;
use CultuurNet\UDB3\Event\ReadModel\DocumentRepositoryInterface;
use CultuurNet\UDB3\Iri\IriGeneratorInterface;
use CultuurNet\UDB3\Place\ReadModel\Relations\RepositoryInterface as PlaceRelationsRepositoryInterface;

class LocalPlaceServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DocumentRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $documentRepository;

    /**
     * @var RepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $placeRepository;

    /**
     * @var IriGeneratorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $iriGenerator;

    /**
     * @var PlaceRelationsRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $placeRelationsRepository;

    /**
     * @var LocalPlaceService
     */
    private $localPlaceService;

    protected function setUp()
    {
        $this->documentRepository = $this->createMock(
            DocumentRepositoryInterface::class
        );

        $this->placeRepository = $this->createMock(RepositoryInterface::class);

        $this->placeRelationsRepository = $this->createMock(
            PlaceRelationsRepositoryInterface::class
        );

        $this->iriGenerator = $this->createMock(IriGeneratorInterface::class);

        $this->localPlaceService = new LocalPlaceService(
            $this->documentRepository,
            $this->placeRepository,
            $this->placeRelationsRepository,
            $this->iriGenerator
        );
    }

    /**
     * @test
     */
    public function it_returns_places_organized_by_organizer()
    {
        $expectedPlaces = ['placeId1', 'placeId2'];

        $this->placeRelationsRepository->expects($this->once())
            ->method('getPlacesOrganizedByOrganizer')
            ->with('organizerId')
            ->willReturn($expectedPlaces);

        $places = $this->localPlaceService->placesOrganizedByOrganizer('organizerId');

        $this->assertEquals($expectedPlaces, $places);
    }
}
