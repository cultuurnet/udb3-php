<?php

namespace CultuurNet\UDB3\Event\ReadModel\Permission;

use Broadway\Domain\DomainMessage;
use Broadway\Domain\Metadata;
use CultuurNet\UDB3\Address\Address;
use CultuurNet\UDB3\Address\Locality;
use CultuurNet\UDB3\Address\PostalCode;
use CultuurNet\UDB3\Address\Street;
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
use CultuurNet\UDB3\Title;
use ValueObjects\Geography\Country;
use ValueObjects\String\String as StringLiteral;

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

        $userId = new StringLiteral('123');

        $this->userIdResolver->expects($this->once())
            ->method('resolveCreatedByToUserId')
            ->with(new StringLiteral('gentonfiles@gmail.com'))
            ->willReturn($userId);

        $this->repository->expects($this->once())
            ->method('markOfferEditableByUser')
            ->with(
                new StringLiteral('dcd1ef37-0608-4824-afe3-99124feda64b'),
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
            ->with(new StringLiteral('gentonfiles@gmail.com'))
            ->willReturn(null);

        $this->repository->expects($this->never())
            ->method('markOfferEditableByUser');

        $this->projector->handle($msg);
    }

    /**
     * @test
     */
    public function it_adds_permission_to_the_user_identified_by_the_createdby_element_for_events_created_from_cdbxml()
    {
        $cdbXmlVersion = '3.2';
        $eventId = new StringLiteral('dcd1ef37-0608-4824-afe3-99124feda64b');
        $createdBy = new StringLiteral('gentonfiles@gmail.com');

        $cdbXml = file_get_contents(__DIR__ . '/../../samples/event_with_photo.cdbxml.xml');

        $event = EventItemFactory::createEventFromCdbXml(
            \CultureFeed_Cdb_Xml::namespaceUriForVersion($cdbXmlVersion),
            $cdbXml
        );

        $cdbXml = new \CultureFeed_Cdb_Default($cdbXmlVersion);
        $cdbXml->addItem($event);

        $payload = new EventCreatedFromCdbXml(
            $eventId,
            new EventXmlString((string)$cdbXml),
            new StringLiteral(\CultureFeed_Cdb_Xml::namespaceUriForVersion($cdbXmlVersion))
        );

        $msg = DomainMessage::recordNow(
            $eventId->toNative(),
            1,
            new Metadata(['user_id' => 'foo']),
            $payload
        );

        $userId = new StringLiteral('123');

        $this->userIdResolver->expects($this->once())
            ->method('resolveCreatedByToUserId')
            ->with($createdBy)
            ->willReturn($userId);

        $this->repository->expects($this->once())
            ->method('markOfferEditableByUser')
            ->with(
                $eventId,
                $userId
            );

        $this->projector->handle($msg);
    }

    /**
     * @test
     */
    public function it_does_not_add_any_permissions_for_events_created_from_cdbxml_with_unresolvable_createdby_value()
    {
        $cdbXmlVersion = '3.2';
        $eventId = new StringLiteral('dcd1ef37-0608-4824-afe3-99124feda64b');
        $createdBy = new StringLiteral('gentonfiles@gmail.com');

        $cdbXml = file_get_contents(__DIR__ . '/../../samples/event_with_photo.cdbxml.xml');

        $event = EventItemFactory::createEventFromCdbXml(
            \CultureFeed_Cdb_Xml::namespaceUriForVersion($cdbXmlVersion),
            $cdbXml
        );

        $cdbXml = new \CultureFeed_Cdb_Default($cdbXmlVersion);
        $cdbXml->addItem($event);

        $payload = new EventCreatedFromCdbXml(
            $eventId,
            new EventXmlString((string)$cdbXml),
            new StringLiteral(\CultureFeed_Cdb_Xml::namespaceUriForVersion($cdbXmlVersion))
        );

        $msg = DomainMessage::recordNow(
            $eventId->toNative(),
            1,
            new Metadata(['user_id' => 'foo']),
            $payload
        );

        $this->userIdResolver->expects($this->once())
            ->method('resolveCreatedByToUserId')
            ->with($createdBy)
            ->willReturn(null);

        $this->repository->expects($this->never())
            ->method('markOfferEditableByUser');

        $this->projector->handle($msg);
    }

    /**
     * @test
     */
    public function it_adds_permission_to_the_user_that_submitted_the_cdbxml_for_events_created_from_cdbxml_without_createdby()
    {
        $cdbXmlVersion = '3.2';
        $eventId = new StringLiteral('dcd1ef37-0608-4824-afe3-99124feda64b');
        $userIdWhileSubmittingCdbXml = new StringLiteral('foo');

        $cdbXml = file_get_contents(__DIR__ . '/../../samples/event_with_photo.cdbxml.xml');

        $event = EventItemFactory::createEventFromCdbXml(
            \CultureFeed_Cdb_Xml::namespaceUriForVersion($cdbXmlVersion),
            $cdbXml
        );
        $event->setCreatedBy(null);

        $cdbXml = new \CultureFeed_Cdb_Default($cdbXmlVersion);
        $cdbXml->addItem($event);

        $payload = new EventCreatedFromCdbXml(
            $eventId,
            new EventXmlString((string)$cdbXml),
            new StringLiteral(\CultureFeed_Cdb_Xml::namespaceUriForVersion($cdbXmlVersion))
        );

        $msg = DomainMessage::recordNow(
            $eventId->toNative(),
            1,
            new Metadata(
                ['user_id' => $userIdWhileSubmittingCdbXml->toNative()]
            ),
            $payload
        );

        $this->userIdResolver->expects($this->never())
            ->method('resolveCreatedByToUserId');

        $this->repository->expects($this->once())
            ->method('markOfferEditableByUser')
            ->with(
                $eventId,
                $userIdWhileSubmittingCdbXml
            );

        $this->projector->handle($msg);
    }

    /**
     * @test
     */
    public function it_add_permission_to_the_user_that_created_an_event()
    {
        $userId = new StringLiteral('user-id');
        $eventId = new StringLiteral('event-id');

        $payload = new EventCreated(
            $eventId->toNative(),
            new Title('test 123'),
            new EventType('0.50.4.0.0', 'concert'),
            new Location(
                '395fe7eb-9bac-4647-acae-316b6446a85e',
                new StringLiteral('Repeteerkot'),
                new Address(
                    new Street('Kerkstraat 69'),
                    new PostalCode('9620'),
                    new Locality('Zottegem'),
                    Country::fromNative('BE')
                )
            ),
            new Calendar('permanent', '', '')
        );

        $msg = DomainMessage::recordNow(
            $eventId->toNative(),
            1,
            new Metadata(
                ['user_id' => $userId->toNative()]
            ),
            $payload
        );

        $this->repository->expects($this->once())
            ->method('markOfferEditableByUser')
            ->with(
                $eventId,
                $userId
            );

        $this->projector->handle($msg);
    }
}
