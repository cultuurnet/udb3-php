<?php

namespace CultuurNet\UDB3\MyOrganizers\ReadModel;

use CultuurNet\UDB3\Cdb\CreatedByToUserIdResolverInterface;
use CultuurNet\UDB3\Cdb\ItemBaseAdapterFactory;
use CultuurNet\UDB3\EventSourcing\DomainMessageBuilder;
use CultuurNet\UDB3\Organizer\Events\OrganizerImportedFromUDB2;

class UDB2ProjectorTest extends \PHPUnit_Framework_TestCase
{
    private const USER_ID = '1adf21b4-711d-4e33-b9ef-c96843582a56';

    /**
     * @var \CultuurNet\UDB3\MyOrganizers\ReadModel\UDB2Projector
     */
    private $projector;

    /**
     * @var RepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $repository;

    /**
     * @var DomainMessageBuilder
     */
    private $domainMessageBuilder;

    /**
     * @var \CultuurNet\UDB3\Cdb\CreatedByToUserIdResolverInterface
     */
    private $userIdResolver;

    /**
     * @inheritdoc
     */
    public function setUp()
    {
        $this->userIdResolver = $this->createMock(CreatedByToUserIdResolverInterface::class);
        $itemBaseAdapterFactory = new ItemBaseAdapterFactory(
            $this->userIdResolver
        );

        $this->repository = $this->createMock(RepositoryInterface::class);
        $this->projector = new UDB2Projector(
            $this->repository,
            $itemBaseAdapterFactory
        );

        // We don't explicitly set a user id in the domain message
        // builder here, because it's irrelevant for events coming from UDB2.
        $this->domainMessageBuilder = new DomainMessageBuilder();
    }

    /**
     * @test
     */
    public function it_updates_the_index_with_organizers_imported_from_udb2()
    {
        $cdbid = 'd53c2bc9-8f0e-4c9a-8457-77e8b3cab3d1';

        $this->userIdResolver->expects($this->once())
            ->method('resolveCreatedByToUserId')
            ->with('info@example.be')
            ->willReturn(self::USER_ID);

        $this->repository->expects($this->once())
            ->method('add')
            ->with(
                $cdbid,
                self::USER_ID,
                new \DateTime(
                    '2014-03-18T12:30:04',
                    new \DateTimeZone('Europe/Brussels')
                )
            );
        
        $this->repository->expects($this->once())
            ->method('setUpdateDate')
            ->with(
                $cdbid,
                new \DateTime(
                    '2014-06-30T11:49:28',
                    new \DateTimeZone('Europe/Brussels')
                )
            );

        $this->projector->handle(
            $this->domainMessageBuilder->create(
                new OrganizerImportedFromUDB2(
                    $cdbid,
                    file_get_contents(__DIR__ . '/organizer.xml'),
                    'http://www.cultuurdatabank.com/XMLSchema/CdbXSD/3.3/FINAL'
                )
            )
        );
    }
}
