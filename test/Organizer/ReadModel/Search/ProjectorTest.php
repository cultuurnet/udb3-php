<?php

namespace CultuurNet\UDB3\Organizer\ReadModel\Search;

use Broadway\Domain\DateTime;
use Broadway\Domain\DomainMessage;
use Broadway\Domain\Metadata;
use CultuurNet\UDB3\Address;
use CultuurNet\UDB3\ContactPoint;
use CultuurNet\UDB3\Organizer\Events\OrganizerCreatedWithUniqueWebsite;
use CultuurNet\UDB3\Organizer\Events\OrganizerDeleted;
use CultuurNet\UDB3\Title;
use PHPUnit_Framework_MockObject_MockObject;
use ValueObjects\Web\Url;

class ProjectorTest extends \PHPUnit_Framework_TestCase
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
     * @var string
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
        $this->uuid = '9196cb78-4381-11e6-beb8-9e71128cae77';
    }

    /**
     * @test
     */
    public function it_can_project_a_created_organizer_with_unique_website()
    {
        $organizerCreated = new OrganizerCreatedWithUniqueWebsite(
            $this->uuid,
            Url::fromNative('http://www.stuk.be'),
            new Title('Het Stuk'),
            [new Address('$street', '$postalCode', '$locality', '$country')],
            new ContactPoint(['050/123'], ['test@test.be', 'test2@test.be'], ['http://www.google.be'])
        );

        $this->repository
            ->expects($this->once())
            ->method('save')
            ->with($this->uuid, 'Het Stuk', 'http://www.stuk.be');

        $this->projector->applyOrganizerCreatedWithUniqueWebsite($organizerCreated, $this->domainMessage);
    }

    /**
     * @test
     */
    public function it_can_project_a_deleted_organizer()
    {
        $organizerDeleted = new OrganizerDeleted(
            $this->uuid
        );

        $this->repository
            ->expects($this->once())
            ->method('remove')
            ->with($this->uuid);

        $this->projector->applyOrganizerDeleted($organizerDeleted, $this->domainMessage);
    }
}
