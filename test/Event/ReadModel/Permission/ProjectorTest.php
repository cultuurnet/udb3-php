<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Event\ReadModel\Permission;

use Broadway\Domain\DomainMessage;
use Broadway\Domain\Metadata;
use CultuurNet\UDB3\Cdb\CreatedByToUserIdResolverInterface;
use CultuurNet\UDB3\Event\Events\EventImportedFromUDB2;
use ValueObjects\String\String;

class ProjectorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var PermissionRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $repository;

    /**
     * @var Projector
     */
    private $projector;

    /**
     * @var CreatedByToUserIdResolverInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $userIdResolver;

    public function setUp()
    {
        $this->repository = $this->getMock(PermissionRepositoryInterface::class);
        $this->userIdResolver = $this->getMock(CreatedByToUserIdResolverInterface::class);

        $this->projector = new Projector(
            $this->repository,
            $this->userIdResolver
        );
    }

    /**
     * @test
     */
    public function it_adds_permission_to_the_user_identified_by_the_createdby_element_for_events_imported_from_udb2()
    {
        $cdbXml = file_get_contents(__DIR__ . '/../../samples/event_with_photo.cdbxml.xml');
        $cdbXmlNamespaceUri = \CultureFeed_Cdb_Xml::namespaceUriForVersion('3.2');

        $payload = new EventImportedFromUDB2(
            'dcd1ef37-0608-4824-afe3-99124feda64b',
            $cdbXml,
            $cdbXmlNamespaceUri
        );
        $msg = DomainMessage::recordNow(
            'dcd1ef37-0608-4824-afe3-99124feda64b',
            1,
            new Metadata(),
            $payload
        );

        $userId = new String('123');

        $this->userIdResolver->expects($this->once())
            ->method('resolveCreatedByToUserId')
            ->with(new String('gentonfiles@gmail.com'))
            ->willReturn($userId);

        $this->repository->expects($this->once())
            ->method('markEventEditableByUser')
            ->with(
                new String('dcd1ef37-0608-4824-afe3-99124feda64b'),
                $userId
            );

        $this->projector->handle($msg);
    }

    /**
     * @test
     */
    public function it_does_not_add_any_permissions_for_events_imported_from_udb2_with_unresolvable_createdby_value()
    {
        $cdbXml = file_get_contents(__DIR__ . '/../../samples/event_with_photo.cdbxml.xml');
        $cdbXmlNamespaceUri = \CultureFeed_Cdb_Xml::namespaceUriForVersion('3.2');

        $payload = new EventImportedFromUDB2(
            'dcd1ef37-0608-4824-afe3-99124feda64b',
            $cdbXml,
            $cdbXmlNamespaceUri
        );
        $msg = DomainMessage::recordNow(
            'dcd1ef37-0608-4824-afe3-99124feda64b',
            1,
            new Metadata(),
            $payload
        );

        $this->userIdResolver->expects($this->once())
            ->method('resolveCreatedByToUserId')
            ->with(new String('gentonfiles@gmail.com'))
            ->willReturn(null);

        $this->repository->expects($this->never())
            ->method('markEventEditableByUser');

        $this->projector->handle($msg);
    }
}
