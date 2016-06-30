<?php

namespace CultuurNet\UDB3\Role\ReadModel\Detail;

use Broadway\Domain\DateTime as BroadwayDateTime;
use Broadway\Domain\DomainMessage;
use Broadway\Domain\Metadata;
use CultuurNet\UDB3\Event\ReadModel\DocumentRepositoryInterface;
use CultuurNet\UDB3\ReadModel\JsonDocument;
use CultuurNet\UDB3\Role\Events\AbstractEvent;
use CultuurNet\UDB3\Role\Events\RoleCreated;
use stdClass;
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
}
