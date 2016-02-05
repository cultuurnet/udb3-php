<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Place\ReadModel\Permission;

use Broadway\Domain\DomainMessage;
use Broadway\Domain\Metadata;
use CultuurNet\UDB3\Address;
use CultuurNet\UDB3\Calendar;
use CultuurNet\UDB3\Cdb\CreatedByToUserIdResolverInterface;
use CultuurNet\UDB3\Cdb\EventItemFactory;
use CultuurNet\UDB3\Event\Events\EventCreated;
use CultuurNet\UDB3\Event\Events\EventCreatedFromCdbXml;
use CultuurNet\UDB3\Event\Events\EventImportedFromUDB2;
use CultuurNet\UDB3\Event\EventType;
use CultuurNet\UDB3\EventXmlString;
use CultuurNet\UDB3\Location;
use CultuurNet\UDB3\Offer\ReadModel\Permission\PermissionRepositoryInterface;
use CultuurNet\UDB3\Place\Events\PlaceCreated;
use CultuurNet\UDB3\Place\Events\PlaceImportedFromUDB2;
use CultuurNet\UDB3\Place\Events\PlaceImportedFromUDB2Event;
use CultuurNet\UDB3\Title;
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
    public function it_adds_permission_to_the_user_identified_by_the_createdby_element_for_places_imported_from_udb2_actor()
    {
        $cdbXml = file_get_contents(__DIR__ . '/../../actor.xml');
        $cdbXmlNamespaceUri = \CultureFeed_Cdb_Xml::namespaceUriForVersion('3.2');

        $payload = new PlaceImportedFromUDB2(
            '318F2ACB-F612-6F75-0037C9C29F44087A',
            $cdbXml,
            $cdbXmlNamespaceUri
        );
        $msg = DomainMessage::recordNow(
            '318F2ACB-F612-6F75-0037C9C29F44087A',
            1,
            new Metadata(),
            $payload
        );

        $userId = new String('123');

        $this->userIdResolver->expects($this->once())
            ->method('resolveCreatedByToUserId')
            ->with(new String('cultuurnet001'))
            ->willReturn($userId);

        $this->repository->expects($this->once())
            ->method('markOfferEditableByUser')
            ->with(
                new String('318F2ACB-F612-6F75-0037C9C29F44087A'),
                $userId
            );

        $this->projector->handle($msg);
    }

    /**
     * @test
     */
    public function it_does_not_add_any_permissions_for_actor_places_imported_from_udb2_with_unresolvable_createdby_value()
    {
        $cdbXml = file_get_contents(__DIR__ . '/../../actor.xml');
        $cdbXmlNamespaceUri = \CultureFeed_Cdb_Xml::namespaceUriForVersion('3.2');

        $payload = new PlaceImportedFromUDB2(
            '318F2ACB-F612-6F75-0037C9C29F44087A',
            $cdbXml,
            $cdbXmlNamespaceUri
        );
        $msg = DomainMessage::recordNow(
            '318F2ACB-F612-6F75-0037C9C29F44087A',
            1,
            new Metadata(),
            $payload
        );

        $this->userIdResolver->expects($this->once())
            ->method('resolveCreatedByToUserId')
            ->with(new String('cultuurnet001'))
            ->willReturn(null);

        $this->repository->expects($this->never())
            ->method('markOfferEditableByUser');

        $this->projector->handle($msg);
    }

    /**
     * @test
     */
    public function it_adds_permission_to_the_user_identified_by_the_createdby_element_for_places_imported_from_udb2()
    {
        $cdbXml = file_get_contents(__DIR__ . '/../../event_with_cdb_externalid.cdbxml.xml');
        $cdbXmlNamespaceUri = \CultureFeed_Cdb_Xml::namespaceUriForVersion('3.2');

        $payload = new PlaceImportedFromUDB2Event(
            '7914ed2d-9f28-4946-b9bd-ae8f7a4aea11',
            $cdbXml,
            $cdbXmlNamespaceUri
        );
        $msg = DomainMessage::recordNow(
            '7914ed2d-9f28-4946-b9bd-ae8f7a4aea11',
            1,
            new Metadata(),
            $payload
        );

        $userId = new String('123');

        $this->userIdResolver->expects($this->once())
            ->method('resolveCreatedByToUserId')
            ->with(new String('manu.roegiers@luca-arts.be'))
            ->willReturn($userId);

        $this->repository->expects($this->once())
            ->method('markOfferEditableByUser')
            ->with(
                new String('7914ed2d-9f28-4946-b9bd-ae8f7a4aea11'),
                $userId
            );

        $this->projector->handle($msg);
    }

    /**
     * @test
     */
    public function it_does_not_add_any_permissions_for_places_imported_from_udb2_with_unresolvable_createdby_value()
    {
        $cdbXml = file_get_contents(__DIR__ . '/../../event_with_cdb_externalid.cdbxml.xml');
        $cdbXmlNamespaceUri = \CultureFeed_Cdb_Xml::namespaceUriForVersion('3.2');

        $payload = new PlaceImportedFromUDB2Event(
            '7914ed2d-9f28-4946-b9bd-ae8f7a4aea11',
            $cdbXml,
            $cdbXmlNamespaceUri
        );
        $msg = DomainMessage::recordNow(
            '7914ed2d-9f28-4946-b9bd-ae8f7a4aea11',
            1,
            new Metadata(),
            $payload
        );

        $this->userIdResolver->expects($this->once())
            ->method('resolveCreatedByToUserId')
            ->with(new String('manu.roegiers@luca-arts.be'))
            ->willReturn(null);

        $this->repository->expects($this->never())
            ->method('markOfferEditableByUser');

        $this->projector->handle($msg);
    }

    /**
     * @test
     */
    public function it_add_permission_to_the_user_that_created_a_place()
    {
        $userId = new String('user-id');
        $placeId = new String('place-id');

        $payload = new PlaceCreated(
            $placeId->toNative(),
            new Title('test 123'),
            new EventType('0.50.4.0.0', 'concert'),
            new Address('$street', '$postalcode', '$locality', '$country'),
            new Calendar('permanent', '', '')
        );

        $msg = DomainMessage::recordNow(
            $placeId->toNative(),
            1,
            new Metadata(
                ['user_id' => $userId->toNative()]
            ),
            $payload
        );

        $this->repository->expects($this->once())
            ->method('markOfferEditableByUser')
            ->with(
                $placeId,
                $userId
            );

        $this->projector->handle($msg);
    }
}
