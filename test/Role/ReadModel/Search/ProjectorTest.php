<?php

namespace CultuurNet\UDB3\Role\ReadModel\Search;

use Broadway\Domain\DateTime;
use Broadway\Domain\DomainMessage;
use Broadway\Domain\Metadata;
use CultuurNet\UDB3\Role\Events\RoleCreated;
use CultuurNet\UDB3\Role\Events\RoleDeleted;
use CultuurNet\UDB3\Role\Events\RoleRenamed;
use PHPUnit_Framework_MockObject_MockObject;
use PHPUnit_Framework_TestCase;
use ValueObjects\Identity\UUID;
use ValueObjects\String\String as StringLiteral;

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
        $this->repository = $this->getMock(RepositoryInterface::class);
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
            ->method('update')
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
}
