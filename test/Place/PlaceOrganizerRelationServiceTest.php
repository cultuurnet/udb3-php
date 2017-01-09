<?php

namespace CultuurNet\UDB3\Place;

use CultuurNet\UDB3\Place\ReadModel\Relations\RepositoryInterface;

class PlaceOrganizerRelationServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var PlaceEditingServiceInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $editService;

    /**
     * @var RepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $relationRepository;

    /**
     * @var PlaceOrganizerRelationService
     */
    private $organizerRelationService;

    public function setUp()
    {
        $this->editService = $this->createMock(PlaceEditingServiceInterface::class);
        $this->relationRepository = $this->createMock(RepositoryInterface::class);

        $this->organizerRelationService = new PlaceOrganizerRelationService(
            $this->editService,
            $this->relationRepository
        );
    }

    /**
     * @test
     */
    public function it_removes_the_organizer_from_all_places()
    {
        $organizerId = 'organizer-1';
        $placeIds = ['place-1', 'place-2'];

        $this->relationRepository->expects($this->once())
            ->method('getPlacesOrganizedByOrganizer')
            ->with($organizerId)
            ->willReturn($placeIds);

        $this->editService->expects($this->exactly(2))
            ->method('deleteOrganizer')
            ->withConsecutive(
                [
                    $placeIds[0],
                    $organizerId,
                ],
                [
                    $placeIds[1],
                    $organizerId,
                ]
            );

        $this->organizerRelationService->deleteOrganizer($organizerId);
    }
}
