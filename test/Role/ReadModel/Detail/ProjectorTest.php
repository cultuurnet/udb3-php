<?php

namespace CultuurNet\UDB3\Role\ReadModel\Detail;

use Broadway\Domain\DateTime as BroadwayDateTime;
use Broadway\Domain\DomainMessage;
use Broadway\Domain\Metadata;
use CultuurNet\UDB3\Event\ReadModel\DocumentRepositoryInterface;
use CultuurNet\UDB3\ReadModel\JsonDocument;
use CultuurNet\UDB3\Role\Events\AbstractEvent;
use CultuurNet\UDB3\Role\Events\ConstraintCreated;
use CultuurNet\UDB3\Role\Events\ConstraintRemoved;
use CultuurNet\UDB3\Role\Events\ConstraintUpdated;
use CultuurNet\UDB3\Role\Events\RoleCreated;
use CultuurNet\UDB3\Role\Events\RoleDeleted;
use CultuurNet\UDB3\Role\Events\RoleRenamed;
use stdClass;
use ValueObjects\Identity\UUID;
use ValueObjects\String\String as StringLiteral;

class ProjectorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var StringLiteral
     */
    private $constraintName;

    /**
     * @var UUID
     */
    private $constraintUuid;

    /**
     * @var DocumentRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $repository;

    /**
     * @var UUID
     */
    private $uuid;

    /**
     * @var StringLiteral
     */
    private $name;

    /**
     * @var Projector
     */
    private $projector;

    public function setUp()
    {
        parent::setUp();

        $this->uuid = new UUID();
        $this->name = new StringLiteral('roleName');

        $this->constraintUuid = new UUID();
        $this->constraintName = new StringLiteral('constraintName');
        
        $this->repository = $this->getMock(
            DocumentRepositoryInterface::class
        );

        $this->projector = new Projector($this->repository);
    }

    /**
     * @test
     */
    public function it_handles_created_when_uuid_unique()
    {
        $roleCreated = new RoleCreated(
            $this->uuid,
            $this->name
        );

        $domainMessage = $this->createDomainMessage(
            $this->uuid,
            $roleCreated,
            BroadwayDateTime::fromString('2016-06-30T13:25:21+01:00')
        );


        $document = new JsonDocument($this->uuid->toNative());

        $json = $document->getBody();
        $json->{'@id'} = $this->uuid->toNative();
        $json->name = (object)[
            'nl' => $this->name->toNative()
        ];
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
    public function it_handles_rename()
    {
        $roleCreated = new RoleCreated(
            $this->uuid,
            $this->name
        );

        $name = new StringLiteral('newRoleName');
        $roleRenamed = new RoleRenamed(
            $this->uuid,
            $name
        );

        $domainMessage = $this->createDomainMessage(
            $this->uuid,
            $roleCreated,
            BroadwayDateTime::fromString('2016-06-30T13:25:21+01:00')
        );
        
        $document = new JsonDocument($this->uuid->toNative());

        $this->projector->handle($domainMessage);

        $domainMessageRenamed = $this->createDomainMessage(
            $this->uuid,
            $roleRenamed,
            BroadwayDateTime::fromString('2016-06-30T14:25:21+01:00')
        );

        $json = $document->getBody();
        $json->{'@id'} = $this->uuid->toNative();
        $json->name = (object)[
            'nl' => $name->toNative()
        ];
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

        $this->projector->handle($domainMessageRenamed);
    }

    /**
     * @test
     */
    public function it_handles_delete()
    {
        $roleCreated = new RoleCreated(
            $this->uuid,
            $this->name
        );

        $domainMessage = $this->createDomainMessage(
            $this->uuid,
            $roleCreated,
            BroadwayDateTime::fromString('2016-06-30T13:25:21+01:00')
        );

        $this->projector->handle($domainMessage);

        $roleDeleted = new RoleDeleted(
            $this->uuid
        );

        $deletedDomainMessage = $this->createDomainMessage(
            $this->uuid,
            $roleDeleted,
            BroadwayDateTime::fromString('2016-06-30T16:25:21+01:00')
        );

        $this->repository->expects($this->once())
            ->method('remove')
            ->with($this->uuid->toNative());

        $this->projector->handle($deletedDomainMessage);
    }

    /**
     * @test
     */
    public function it_handles_constraint_created()
    {
        $constraintCreated = new ConstraintCreated(
            $this->uuid,
            $this->constraintName
        );

        $domainMessage = $this->createDomainMessage(
            $this->uuid,
            $constraintCreated,
            BroadwayDateTime::fromString('2016-06-30T13:25:21+01:00')
        );

        $document = new JsonDocument($this->uuid->toNative());

        $json = $document->getBody();
        $json->{'@id'} = $this->uuid->toNative();
        $json->name = (object)[
            'nl' => $this->name->toNative()
        ];
        $json->created = '2016-06-30T13:25:21+01:00';
        $json->modified = '2016-06-30T13:25:21+01:00';
        $json->constraint = $this->constraintName->toNative();

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
    public function it_handles_constraint_updated()
    {
        $constraintUpdated = new ConstraintUpdated(
            $this->uuid,
            new StringLiteral('newConstraintName')
        );

        $domainMessage = $this->createDomainMessage(
            $this->uuid,
            $constraintUpdated,
            BroadwayDateTime::fromString('2016-06-30T13:25:21+01:00')
        );

        $document = new JsonDocument($this->uuid->toNative());

        $json = $document->getBody();
        $json->{'@id'} = $this->uuid->toNative();
        $json->name = (object)[
            'nl' => $this->name->toNative()
        ];
        $json->created = '2016-06-30T13:25:21+01:00';
        $json->modified = '2016-06-30T13:25:21+01:00';
        $json->constraint = 'newConstraintName';

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
    public function it_handles_constraint_removed()
    {
        $constraintRemoved = new ConstraintRemoved(
            $this->uuid
        );

        $domainMessage = $this->createDomainMessage(
            $this->uuid,
            $constraintRemoved,
            BroadwayDateTime::fromString('2016-06-30T13:25:21+01:00')
        );

        $document = new JsonDocument($this->uuid->toNative());

        $json = $document->getBody();
        $json->{'@id'} = $this->uuid->toNative();
        $json->name = (object)[
            'nl' => $this->name->toNative()
        ];
        $json->created = '2016-06-30T13:25:21+01:00';
        $json->modified = '2016-06-30T13:25:21+01:00';

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
        $json->name = (object)[
            'nl' => $this->name->toNative()
        ];
        $json->created = '2016-06-30T13:25:21+01:00';
        $json->modified = '2016-06-30T13:25:21+01:00';

        $document = $document->withBody($json);

        return $document;
    }
}