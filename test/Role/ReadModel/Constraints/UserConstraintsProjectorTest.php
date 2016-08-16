<?php

namespace CultuurNet\UDB3\Role\ReadModel\Constraints;

use Broadway\Domain\DateTime as BroadwayDateTime;
use Broadway\Domain\DomainMessage;
use Broadway\Domain\Metadata;
use Broadway\Serializer\SerializableInterface;
use CultuurNet\UDB3\Role\Events\ConstraintCreated;
use CultuurNet\UDB3\Role\Events\ConstraintRemoved;
use CultuurNet\UDB3\Role\Events\ConstraintUpdated;
use CultuurNet\UDB3\Role\Events\RoleDeleted;
use ValueObjects\Identity\UUID;
use ValueObjects\String\String as StringLiteral;

class UserConstraintsProjectorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var UserConstraintsWriteRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $userConstraintWriteRepository;

    /**
     * @var UserConstraintsProjector
     */
    private $userConstraintProjector;

    protected function setUp()
    {
        $this->userConstraintWriteRepository = $this->getMock(
            UserConstraintsWriteRepositoryInterface::class
        );

        $this->userConstraintProjector = new UserConstraintsProjector(
            $this->userConstraintWriteRepository
        );
    }

    /**
     * @test
     */
    public function it_calls_insert_role_on_constraint_created_event()
    {
        $constraintCreated = new ConstraintCreated(
            new UUID(),
            new StringLiteral('zipCode:3000')
        );
        $domainMessage = $this->createDomainMessage($constraintCreated);

        $this->userConstraintWriteRepository->expects($this->once())
            ->method('insertRole')
            ->with($constraintCreated->getUuid(), $constraintCreated->getQuery());

        $this->userConstraintProjector->handle($domainMessage);
    }

    /**
     * @test
     */
    public function it_calls_update_role_on_constraint_updated_event()
    {
        $constraintUpdated = new ConstraintUpdated(
            new UUID(),
            new StringLiteral('zipCode:3000')
        );
        $domainMessage = $this->createDomainMessage($constraintUpdated);

        $this->userConstraintWriteRepository->expects($this->once())
            ->method('updateRole')
            ->with($constraintUpdated->getUuid(), $constraintUpdated->getQuery());

        $this->userConstraintProjector->handle($domainMessage);
    }

    /**
     * @test
     */
    public function it_calls_remove_role_on_constraint_removed_event()
    {
        $constraintRemoved = new ConstraintRemoved(new UUID());
        $domainMessage = $this->createDomainMessage($constraintRemoved);

        $this->userConstraintWriteRepository->expects($this->once())
            ->method('removeRole')
            ->with($constraintRemoved->getUuid());

        $this->userConstraintProjector->handle($domainMessage);
    }

    /**
     * @test
     */
    public function it_calls_remove_role_on_role_deleted_event()
    {
        $roleDeleted = new RoleDeleted(new UUID());
        $domainMessage = $this->createDomainMessage($roleDeleted);

        $this->userConstraintWriteRepository->expects($this->once())
            ->method('removeRole')
            ->with($roleDeleted->getUuid());

        $this->userConstraintProjector->handle($domainMessage);
    }

    /**
     * @param SerializableInterface $payload
     * @return DomainMessage
     */
    private function createDomainMessage(SerializableInterface $payload)
    {
        return new DomainMessage(
            'id',
            0,
            new Metadata(),
            $payload,
            BroadwayDateTime::now()
        );
    }
}
