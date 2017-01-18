<?php

namespace CultuurNet\UDB3\Role\ReadModel\Search;

use Broadway\Domain\DateTime;
use Broadway\Domain\DomainMessage;
use Broadway\Domain\Metadata;
use CultuurNet\UDB3\DomainMessage\DomainMessageTestDataTrait;
use CultuurNet\UDB3\Role\Events\ConstraintCreated;
use CultuurNet\UDB3\Role\Events\ConstraintRemoved;
use CultuurNet\UDB3\Role\Events\ConstraintUpdated;
use CultuurNet\UDB3\Role\Events\RoleCreated;
use CultuurNet\UDB3\Role\Events\RoleDeleted;
use CultuurNet\UDB3\Role\Events\RoleRenamed;
use PHPUnit_Framework_MockObject_MockObject;
use PHPUnit_Framework_TestCase;
use ValueObjects\Identity\UUID;
use ValueObjects\StringLiteral\StringLiteral;

class ProjectorTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var DomainMessage
     */
    private $domainMessage;

    /**
     * @var Projector
     */
    private $projector;

    /**
     * @var UUID
     */
    private $uuid;

    /**
     * @var RepositoryInterface|PHPUnit_Framework_MockObject_MockObject
     */
    private $repository;

    public function setUp()
    {
        $this->repository = $this->createMock(RepositoryInterface::class);
        $this->projector = new Projector($this->repository);
        $this->domainMessage = new DomainMessage('id', 0, new Metadata(), '', DateTime::now());
        $this->uuid = new UUID();
    }

    /**
     * @test
     */
    public function it_can_project_a_created_role()
    {
        $roleCreated = new RoleCreated(
            $this->uuid,
            new StringLiteral('role_name')
        );

        $this->repository
            ->expects($this->once())
            ->method('save')
            ->with($this->uuid->toNative(), 'role_name');

        $this->projector->applyRoleCreated($roleCreated, $this->domainMessage);
    }

    /**
     * @test
     */
    public function it_can_project_a_renamed_role()
    {
        $roleRenamed = new RoleRenamed(
            $this->uuid,
            new StringLiteral('role_name')
        );

        $this->repository
            ->expects($this->once())
            ->method('updateName')
            ->with($this->uuid->toNative(), 'role_name');

        $this->projector->applyRoleRenamed($roleRenamed, $this->domainMessage);
    }

    /**
     * @test
     */
    public function it_can_project_a_deleted_role()
    {
        $roleDeleted = new RoleDeleted(
            $this->uuid
        );

        $this->repository
            ->expects($this->once())
            ->method('remove')
            ->with($this->uuid->toNative());

        $this->projector->applyRoleDeleted($roleDeleted, $this->domainMessage);
    }

    /**
     * @test
     */
    public function it_calls_update_constraint_on_constraint_created_event()
    {
        $constraintCreated = new ConstraintCreated(
            new UUID(),
            new StringLiteral('zipCode:3000')
        );
        $domainMessage = $this->createDomainMessage($constraintCreated);

        $this->repository->expects($this->once())
            ->method('updateConstraint')
            ->with($constraintCreated->getUuid(), $constraintCreated->getQuery());

        $this->projector->handle($domainMessage);
    }

    /**
     * @test
     */
    public function it_calls_update_constraint_on_constraint_updated_event()
    {
        $constraintUpdated = new ConstraintUpdated(
            new UUID(),
            new StringLiteral('zipCode:3000')
        );
        $domainMessage = $this->createDomainMessage($constraintUpdated);

        $this->repository->expects($this->once())
            ->method('updateConstraint')
            ->with($constraintUpdated->getUuid(), $constraintUpdated->getQuery());

        $this->projector->handle($domainMessage);
    }

    /**
     * @test
     */
    public function it_calls_update_constraint_on_constraint_removed_event()
    {
        $constraintRemoved = new ConstraintRemoved(new UUID());
        $domainMessage = $this->createDomainMessage($constraintRemoved);

        $this->repository->expects($this->once())
            ->method('updateConstraint')
            ->with($constraintRemoved->getUuid());

        $this->projector->handle($domainMessage);
    }

    /**
     * @param $payload
     * @return DomainMessage
     */
    private function createDomainMessage($payload)
    {
        return new DomainMessage(
            'id',
            1,
            new Metadata(),
            $payload,
            DateTime::now()
        );
    }
}
