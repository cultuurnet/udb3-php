<?php

namespace CultuurNet\UDB3\Role\ReadModel\Users;

use Broadway\Domain\DateTime as BroadwayDateTime;
use Broadway\Domain\DomainMessage;
use Broadway\Domain\Metadata;
use Broadway\Serializer\SerializableInterface;
use CultuurNet\UDB3\Event\ReadModel\DocumentRepositoryInterface;
use CultuurNet\UDB3\ReadModel\JsonDocument;
use CultuurNet\UDB3\Role\Events\RoleCreated;
use CultuurNet\UDB3\Role\Events\RoleDeleted;
use CultuurNet\UDB3\Role\Events\UserAdded;
use CultuurNet\UDB3\User\UserIdentityResolverInterface;
use ValueObjects\Identity\UUID;
use ValueObjects\String\String as StringLiteral;

class RoleUsersProjectorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DocumentRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $repository;

    /**
     * @var UserIdentityResolverInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $userIdentityResolver;

    /**
     * @var RoleUsersProjector
     */
    private $roleUsersProjector;

    protected function setUp()
    {
        $this->repository = $this->getMock(DocumentRepositoryInterface::class);

        $this->userIdentityResolver = $this->getMock(
            UserIdentityResolverInterface::class
        );

        $this->roleUsersProjector = new RoleUsersProjector(
            $this->repository,
            $this->userIdentityResolver
        );
    }

    /**
     * @test
     */
    public function it_creates_projection_with_empty_list_of_users_on_role_created_event()
    {
        $roleCreated = new RoleCreated(
            new UUID(),
            new StringLiteral('roleName')
        );

        $domainMessage = $this->createDomainMessage(
            $roleCreated->getUuid(),
            $roleCreated
        );

        $jsonDocument = new JsonDocument(
            $roleCreated->getUuid(),
            json_encode([])
        );

        $this->repository->expects($this->once())
            ->method('save')
            ->with($jsonDocument);

        $this->roleUsersProjector->handle($domainMessage);
    }

    /**
     * @test
     */
    public function it_removes_projection_on_role_deleted_event()
    {
        $roleDeleted = new RoleDeleted(
            new UUID()
        );

        $domainMessage = $this->createDomainMessage(
            $roleDeleted->getUuid(),
            $roleDeleted
        );

        $this->repository->expects($this->once())
            ->method('remove')
            ->with($roleDeleted->getUuid());

        $this->roleUsersProjector->handle($domainMessage);
    }

    /**
     * @test
     */
    public function it_updates_projection_with_user_identity_on_user_added_event()
    {
        $userAdded = new UserAdded(
            new UUID(),
            new StringLiteral('userId')
        );

        $domainMessage = $this->createDomainMessage(
            $userAdded->getUuid(),
            $userAdded
        );

        $jsonDocument = new JsonDocument($userAdded->getUuid(), []);

        $this->mockGet($jsonDocument);

        $this->repository->expects($this->once())
            ->method('save')
            ->with($jsonDocument);

        $this->roleUsersProjector->handle($domainMessage);
    }

    /**
     * @test
     */
    public function it_removes_user_identity_from_projection_on_user_removed_event()
    {
        $this->assertTrue(false);
    }

    /**
     * @param JsonDocument $jsonDocument
     */
    private function mockGet(JsonDocument $jsonDocument)
    {
        $this->repository
            ->method('get')
            ->with($jsonDocument->getId())
            ->willReturn($jsonDocument);
    }

    /**
     * @param UUID $uuid
     * @param SerializableInterface $payload
     * @return DomainMessage
     */
    private function createDomainMessage(
        UUID $uuid,
        SerializableInterface $payload
    ) {
        return new DomainMessage(
            $uuid,
            0,
            new Metadata(),
            $payload,
            BroadwayDateTime::now()
        );
    }
}
