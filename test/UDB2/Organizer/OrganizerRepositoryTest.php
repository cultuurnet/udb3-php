<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\UDB2\Organizer;

use Broadway\Repository\AggregateNotFoundException;
use Broadway\Repository\RepositoryInterface;
use CultuurNet\UDB3\Organizer\Organizer;
use CultuurNet\UDB3\UDB2\EntryAPIImprovedFactoryInterface;

class OrganizerRepositoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var OrganizerRepository
     */
    private $organizerRepository;

    /**
     * @var RepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $innerRepository;

    /**
     * @var OrganizerImporterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $organizerImporter;

    public function setUp()
    {
        $this->innerRepository = $this->getMock(RepositoryInterface::class);
        $this->organizerImporter = $this->getMock(
            OrganizerImporterInterface::class
        );
        $this->organizerRepository = new OrganizerRepository(
            $this->innerRepository,
            $this->getMock(EntryAPIImprovedFactoryInterface::class),
            $this->organizerImporter
        );
    }

    /**
     * @test
     */
    public function it_calls_the_importer_if_the_organizer_is_missing_in_the_decorated_repository()
    {
        $id = 'foo';

        $this->innerRepository->expects($this->once())
            ->method('load')
            ->with($id)
            ->willThrowException(
                new AggregateNotFoundException()
            );

        $this->organizerImporter->expects($this->once())
            ->method('createOrganizerFromUDB2')
            ->with($id)
            ->willReturn(new Organizer());

        $this->organizerRepository->load($id);
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

        $this->organizerImporter->expects($this->once())
            ->method('createOrganizerFromUDB2')
            ->with($id);

        $this->setExpectedException(AggregateNotFoundException::class);

        $this->organizerRepository->load($id);
    }
}
