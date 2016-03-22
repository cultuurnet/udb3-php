<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\ReadModel\Index;

use Broadway\Domain\DateTime;
use Broadway\Domain\DomainMessage;
use Broadway\Domain\Metadata;
use CultuurNet\UDB3\Cdb\CreatedByToUserIdResolverInterface;
use CultuurNet\UDB3\Event\Events\EventImportedFromUDB2;
use CultuurNet\UDB3\Event\Events\EventProjectedToJSONLD;
use CultuurNet\UDB3\Organizer\Events\OrganizerImportedFromUDB2;
use CultuurNet\UDB3\Place\Events\PlaceImportedFromUDB2;
use CultuurNet\UDB3\Place\Events\PlaceImportedFromUDB2Event;
use CultuurNet\UDB3\Place\PlaceProjectedToJSONLD;
use Guzzle\Common\Event;
use ValueObjects\Web\Domain;

class ProjectorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var RepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $repository;

    /**
     * @var CreatedByToUserIdResolverInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $userIdResolver;

    /**
     * @var Projector
     */
    private $projector;

    const DATETIME = '2015-08-07T12:01:00.034024+00:00';

    public function setUp()
    {
        $this->repository = $this->getMock(RepositoryInterface::class);
        $this->userIdResolver = $this->getMock(CreatedByToUserIdResolverInterface::class);

        $this->projector = new Projector(
            $this->repository,
            $this->userIdResolver,
            Domain::specifyType('omd.be'),
            Domain::specifyType('udb.be')
        );
    }

    /**
     * @test
     */
    public function it_updates_the_index_with_events_imported_from_udb2()
    {
        $this->userIdResolver->expects($this->once())
            ->method('resolveCreatedByToUserId')
            ->willReturn('user-id-one-two-three');

        $this->repository->expects($this->once())
            ->method('updateIndex')
            ->with(
                '123-456',
                EntityType::EVENT(),
                'user-id-one-two-three',
                'GALLERY TRAEGHE exhibition Janine de Coninck \'BLACK AND WHITE\' 1 - 9 November 2014',
                '8000',
                Domain::specifyType('udb.be'),
                new \DateTime(
                    '2014-09-08T09:10:16',
                    new \DateTimeZone('Europe/Brussels')
                )
            );

        $this->projector->handle(
            $this->domainMessage(
                new EventImportedFromUDB2(
                    '123-456',
                    file_get_contents(__DIR__ . '/event.xml'),
                    'http://www.cultuurdatabank.com/XMLSchema/CdbXSD/3.2/FINAL'
                )
            )
        );
    }

    /**
     * @test
     */
    public function it_should_update_the_index_when_importing_a_place_from_udb2_event()
    {
        $this->userIdResolver->expects($this->once())
            ->method('resolveCreatedByToUserId')
            ->willReturn('user-id-one-two-three');

        $this->repository->expects($this->once())
            ->method('updateIndex')
            ->with(
                'place-lace-ace-ce',
                EntityType::PLACE(),
                'user-id-one-two-three',
                'stuuuk',
                '3000',
                Domain::specifyType('udb.be'),
                new \DateTime(
                    '2016-02-19T10:36:26+0100',
                    new \DateTimeZone('Europe/Brussels')
                )
            );

        $this->projector->handle(
            $this->domainMessage(
                new PlaceImportedFromUDB2Event(
                    'place-lace-ace-ce',
                    file_get_contents(__DIR__ . '/place.xml'),
                    'http://www.cultuurdatabank.com/XMLSchema/CdbXSD/3.3/FINAL'
                )
            )
        );
    }

    /**
     * @test
     */
    public function it_ignores_events_imported_from_udb2_without_a_title()
    {
        $this->repository->expects($this->never())
            ->method('updateIndex');

        $this->projector->handle(
            $this->domainMessage(
                new EventImportedFromUDB2(
                    '123-456',
                    file_get_contents(__DIR__ . '/event-with-empty-title.xml'),
                    'http://www.cultuurdatabank.com/XMLSchema/CdbXSD/3.2/FINAL'
                )
            )
        );
    }

    /**
     * @test
     */
    public function it_should_add_an_indexed_item_when_importing_a_place_from_udb2()
    {
        $this->userIdResolver->expects($this->once())
            ->method('resolveCreatedByToUserId')
            ->willReturn('user-id-one-two-three');

        $this->repository->expects($this->once())
            ->method('updateIndex')
            ->with(
                'place-lace-ace-ce',
                EntityType::PLACE(),
                'user-id-one-two-three',
                'CC Palethe',
                '3900',
                Domain::specifyType('udb.be'),
                new \DateTime(
                    '2010-01-06T13:33:06+0100',
                    new \DateTimeZone('Europe/Brussels')
                )
            );

        $this->projector->handle(
            $this->domainMessage(
                new PlaceImportedFromUDB2(
                    'place-lace-ace-ce',
                    file_get_contents(__DIR__ . '/udb2_place.xml'),
                    'http://www.cultuurdatabank.com/XMLSchema/CdbXSD/3.2/FINAL'
                )
            )
        );
    }

    /**
     * @test
     */
    public function it_should_add_an_indexed_item_when_importing_an_organizer_from_udb2()
    {
        $this->userIdResolver->expects($this->once())
            ->method('resolveCreatedByToUserId')
            ->willReturn('user-id-one-two-three');

        $this->repository->expects($this->once())
            ->method('updateIndex')
            ->with(
                'some-orga-nizer-id',
                EntityType::ORGANIZER(),
                'user-id-one-two-three',
                'DE Studio',
                '',
                Domain::specifyType('udb.be'),
                new \DateTime(
                    '2010-01-06T13:46:00+0100',
                    new \DateTimeZone('Europe/Brussels')
                )
            );

        $this->projector->handle(
            $this->domainMessage(
                new OrganizerImportedFromUDB2(
                    'some-orga-nizer-id',
                    file_get_contents(__DIR__ . '/udb2_organizer.xml'),
                    'http://www.cultuurdatabank.com/XMLSchema/CdbXSD/3.2/FINAL'
                )
            )
        );
    }

    /**
     * @test
     * @dataProvider indexUpdateEventsDataProvider
     */
    public function it_should_set_the_update_date_when_indexed_items_change(
        DomainMessage $domainMessage,
        $itemId
    ) {
        $this->repository->expects($this->once())
            ->method('setUpdateDate')
            ->with($itemId, new \DateTime(self::DATETIME));

        $this->projector->handle($domainMessage);
    }

    public function indexUpdateEventsDataProvider()
    {
        return array(
            array(
                $this->domainMessage(
                    new PlaceProjectedToJSONLD(
                        '6ecf5da4-220d-4486-9327-17c7ec8fa070'
                    )
                ),
                '6ecf5da4-220d-4486-9327-17c7ec8fa070'
            ),
            array(
                $this->domainMessage(
                    new EventProjectedToJSONLD(
                        '6ecf5da4-220d-4486-9327-17c7ec8fa070'
                    )
                ),
                '6ecf5da4-220d-4486-9327-17c7ec8fa070'
            ),
        );
    }

    /**
     * @param mixed $payload
     * @return DomainMessage
     */
    private function domainMessage($payload)
    {
        return new DomainMessage(
            '123',
            1,
            new Metadata(),
            $payload,
            DateTime::fromString(self::DATETIME)
        );
    }
}
