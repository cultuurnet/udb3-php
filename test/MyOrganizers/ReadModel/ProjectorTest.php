<?php

namespace CultuurNet\UDB3\MyOrganizers\ReadModel;

use Broadway\Domain\Metadata;
use CultuurNet\UDB3\EventSourcing\DomainMessageBuilder;
use CultuurNet\UDB3\Language;
use CultuurNet\UDB3\Organizer\Events\OrganizerCreated;
use CultuurNet\UDB3\Organizer\Events\OrganizerCreatedWithUniqueWebsite;
use CultuurNet\UDB3\Organizer\Events\OrganizerDeleted;
use CultuurNet\UDB3\Organizer\Events\OrganizerEvent;
use CultuurNet\UDB3\Organizer\OrganizerProjectedToJSONLD;
use CultuurNet\UDB3\Title;
use ValueObjects\Web\Url;

class ProjectorTest extends \PHPUnit_Framework_TestCase
{
    private const DATETIME = '2018-08-07T12:01:00.034024+00:00';
    private const USER_ID = '1adf21b4-711d-4e33-b9ef-c96843582a56';

    /**
     * @var Projector
     */
    private $projector;

    /**
     * @var RepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $repository;

    /**
     * @var DomainMessageBuilder
     */
    private $domainMessageBuilder;

    /**
     * @inheritdoc
     */
    public function setUp()
    {
        $this->repository = $this->createMock(RepositoryInterface::class);
        $this->projector = new Projector(
            $this->repository
        );

        $this->domainMessageBuilder = (new DomainMessageBuilder())
            ->setRecordedOnFromDateTimeString(self::DATETIME)
            ->setUserId(self::USER_ID);
    }

    /**
     * @test
     */
    public function it_sets_the_readmodel_updated_date_when_organizer_was_projected_to_jsonld()
    {
        $itemId = '2fe1b3e4-45d2-422a-8155-17e271e60315';

        $this->repository->expects($this->once())
            ->method('setUpdateDate')
            ->with(
                $itemId,
                new \DateTime(self::DATETIME)
            );

        $projectedToJSONLD = new OrganizerProjectedToJSONLD(
            $itemId,
            'some-in-this-case-irrelevant-iri'
        );

        $msg = $this->domainMessageBuilder->create($projectedToJSONLD);

        $this->projector->handle($msg);
    }

    public function organizerCreatedEventsDataProvider()
    {
        return [
            'legacy organizer created' => [
                new OrganizerCreated(
                    'dffd8f41-77d7-49b4-832a-cd343bf153e8',
                    new Title('some title'),
                    [],
                    [],
                    [],
                    []
                ),
            ],
            'created with unique website' => [
                new OrganizerCreatedWithUniqueWebsite(
                    'dffd8f41-77d7-49b4-832a-cd343bf153e8',
                    new Language('nl'),
                    Url::fromNative('https://example.com'),
                    new Title('some title')
                ),
            ],
        ];
    }

    /**
     * @dataProvider organizerCreatedEventsDataProvider
     *
     * @test
     */
    public function it_adds_organizer_when_an_organizer_is_created(OrganizerEvent $organizerEvent)
    {
        $msg = $this->domainMessageBuilder->create($organizerEvent);

        // We do not expect a delete first when NOT replaying.
        $this->repository->expects($this->never())
            ->method('delete');

        $this->repository->expects($this->once())
            ->method('add')
            ->with(
                $organizerEvent->getOrganizerId(),
                self::USER_ID,
                new \DateTime(self::DATETIME)
            );

        $this->projector->handle($msg);
    }

    /**
     * @test
     */
    public function it_removes_organizer_from_readmodel_when_organizer_is_deleted()
    {
        $organizerId = 'my-organizer';
        $organizerDeleted = new OrganizerDeleted($organizerId);

        $this->repository->expects($this->once())
            ->method('delete')
            ->with($organizerId);

        $this->projector->handle(
            $this->domainMessageBuilder->create($organizerDeleted)
        );
    }

    /**
     * @test
     */
    public function it_first_deletes_existing_entry_when_replaying()
    {
        $organizerId = 'my-organizer';

        $organizerCreated = new OrganizerCreated(
            $organizerId,
            new Title('some title'),
            [],
            [],
            [],
            []
        );

        $domainMessage = $this->domainMessageBuilder
            ->forReplay()
            ->create($organizerCreated);

        $this->repository->expects($this->once())
            ->method('delete')
            ->with($organizerId);

        $this->repository->expects($this->once())
            ->method('add')
            ->with(
                $organizerCreated->getOrganizerId(),
                self::USER_ID,
                new \DateTime(self::DATETIME)
            );


        $this->projector->handle($domainMessage);
    }
}
