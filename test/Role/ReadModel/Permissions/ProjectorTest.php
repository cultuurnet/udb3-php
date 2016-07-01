<?php

namespace CultuurNet\UDB3\Role\ReadModel\Permissions;

use Broadway\Domain\DateTime as BroadwayDateTime;
use Broadway\Domain\DomainMessage;
use Broadway\Domain\Metadata;
use CultuurNet\UDB3\Event\ReadModel\DocumentRepositoryInterface;
use CultuurNet\UDB3\ReadModel\JsonDocument;
use CultuurNet\UDB3\Role\Events\AbstractEvent;
use CultuurNet\UDB3\Role\Events\PermissionAdded;
use CultuurNet\UDB3\Role\Events\PermissionRemoved;
use CultuurNet\UDB3\Role\Events\RoleCreated;
use CultuurNet\UDB3\Role\ValueObjects\Permission;
use ValueObjects\Identity\UUID;
use ValueObjects\String\String as StringLiteral;

class ProjectorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DocumentRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $repository;

    /**
     * @var UUID
     */
    private $uuid;

    /**
     * @var Permission
     */
    private $permission;

    /**
     * @var Projector
     */
    private $projector;

    public function setUp()
    {
        parent::setUp();

        $this->uuid = new UUID();
        $this->permission = Permission::AANBOD_INVOEREN();

        $this->repository = $this->getMock(
            DocumentRepositoryInterface::class
        );

        $this->projector = new Projector($this->repository);
    }

    public function it_initializes_empty_permissions_on_the_creation_of_a_role()
    {
        $roleCreated = new RoleCreated(
            $this->uuid,
            new StringLiteral('roleName')
        );

        $domainMessage = $this->createDomainMessage(
            $this->uuid,
            $roleCreated,
            BroadwayDateTime::fromString('2016-06-30T13:25:21+01:00')
        );

        $document = new JsonDocument($this->uuid->toNative());

        $json = $document->getBody();
        $json->{'@id'} = $this->uuid->toNative();
        $json->permissions = (object)[];
        $json->created = '2016-06-30T13:25:21+01:00';
        $json->modified = '2016-06-30T13:25:21+01:00';

        $document = $document->withBody($json);

        $this->repository->expects($this->once())
            ->method('save')
            ->with(
                $document
            );

        $this->projector->handle($domainMessage);
    }

    /**
     * @test
     */
    public function it_handles_the_addition_of_a_permission()
    {
        $roleCreated = new RoleCreated(
            $this->uuid,
            new StringLiteral('roleName')
        );

        $domainMessageCreated = $this->createDomainMessage(
            $this->uuid,
            $roleCreated,
            BroadwayDateTime::fromString('2016-06-30T13:25:21+01:00')
        );

        $this->projector->handle($domainMessageCreated);

        $permissionAdded = new PermissionAdded(
            $this->uuid,
            $this->permission
        );

        $domainMessage = $this->createDomainMessage(
            $this->uuid,
            $permissionAdded,
            BroadwayDateTime::fromString('2016-06-30T14:25:21+01:00')
        );
        
        $document = new JsonDocument($this->uuid->toNative());

        $json = $document->getBody();
        $json->{'@id'} = $this->uuid->toNative();
        $json->permissions[$this->permission->getName()] = $this->permission->getValue();
        $json->created = '2016-06-30T13:25:21+01:00';
        $json->modified = '2016-06-30T14:25:21+01:00';

        $document = $document->withBody($json);

        $this->repository->expects($this->once())
            ->method('get')
            ->with($this->uuid->toNative())
            ->willReturn($this->initialDocument());

        $this->repository->expects($this->once())
            ->method('save')
            ->with(
                $document
            );

        $this->projector->handle($domainMessage);
    }

    /**
     * @test
     */
    public function it_handles_the_removal_of_a_permission()
    {
        $permissionAdded = new PermissionAdded(
            $this->uuid,
            $this->permission
        );
        
        $permissionRemoved = new PermissionRemoved(
            $this->uuid,
            $this->permission
        );

        $domainMessage = $this->createDomainMessage(
            $this->uuid,
            $permissionAdded,
            BroadwayDateTime::fromString('2016-06-30T13:25:21+01:00')
        );

        $document = new JsonDocument($this->uuid->toNative());

        $this->projector->handle($domainMessage);

        $domainMessageRemoved = $this->createDomainMessage(
            $this->uuid,
            $permissionRemoved,
            BroadwayDateTime::fromString('2016-06-30T15:25:21+01:00')
        );

        $json = $document->getBody();
        $json->{'@id'} = $this->uuid->toNative();
        $json->permissions = (object)[];
        $json->created = '2016-06-30T13:25:21+01:00';
        $json->modified = '2016-06-30T15:25:21+01:00';

        $document = $document->withBody($json);

        $this->repository->expects($this->once())
            ->method('get')
            ->with($this->uuid->toNative())
            ->willReturn($this->documentWithPermission());

        $this->repository->expects($this->once())
            ->method('save')
            ->with(
                $document
            );

        $this->projector->handle($domainMessageRemoved);
    }

    /**
     * @param string $id
     * @param AbstractEvent $payload
     * @param BroadwayDateTime $dateTime
     * @return DomainMessage
     */
    private function createDomainMessage($id, $payload, BroadwayDateTime $dateTime = null)
    {
        if (null === $dateTime) {
            $dateTime = BroadwayDateTime::now();
        }

        return new DomainMessage(
            $id,
            0,
            new Metadata(),
            $payload,
            $dateTime
        );
    }

    /**
     * @return JsonDocument|static
     */
    private function initialDocument()
    {
        $document = new JsonDocument($this->uuid->toNative());

        $json = $document->getBody();
        $json->{'@id'} = $this->uuid->toNative();
        $json->permissions = (object) [];
        $json->created = '2016-06-30T13:25:21+01:00';
        $json->modified = '2016-06-30T13:25:21+01:00';

        $document = $document->withBody($json);

        return $document;
    }

    /**
     * @return JsonDocument|static
     */
    private function documentWithPermission()
    {
        $document = new JsonDocument($this->uuid->toNative());

        $json = $document->getBody();
        $json->{'@id'} = $this->uuid->toNative();
        $json->permissions[$this->permission->getName()] = $this->permission->getValue();
        $json->created = '2016-06-30T13:25:21+01:00';
        $json->modified = '2016-06-30T14:25:21+01:00';

        $document = $document->withBody($json);

        return $document;
    }
}
