<?php

namespace CultuurNet\UDB3\ReadModel\Index;

use CultuurNet\UDB3\Cdb\CreatedByToUserIdResolverInterface;
use CultuurNet\UDB3\Cdb\ItemBaseAdapterFactory;
use CultuurNet\UDB3\Event\Events\EventImportedFromUDB2;
use CultuurNet\UDB3\EventSourcing\DomainMessageBuilder;
use CultuurNet\UDB3\Place\Events\PlaceImportedFromUDB2;
use PHPUnit_Framework_TestCase;
use ValueObjects\Web\Domain;

class UDB2ProjectorTest extends PHPUnit_Framework_TestCase
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
     * @var UDB2Projector
     */
    private $projector;

    /**
     * @var DomainMessageBuilder
     */
    private $domainMessageBuilder;

    const DATETIME = '2015-08-07T12:01:00.034024+00:00';

    const USER_ID = '1adf21b4-711d-4e33-b9ef-c96843582a56';

    public function setUp()
    {
        $this->repository = $this->createMock(RepositoryInterface::class);
        $this->userIdResolver = $this->createMock(CreatedByToUserIdResolverInterface::class);
        $itemBaseAdapterFactory = new ItemBaseAdapterFactory(
            $this->userIdResolver
        );

        $this->projector = new UDB2Projector(
            $this->repository,
            $itemBaseAdapterFactory,
            Domain::specifyType('udb.be')
        );

        // We don't explicitly set a user id in the domain message
        // builder here, because it's irrelevant for events coming from UDB2.
        $this->domainMessageBuilder = (new DomainMessageBuilder())
            ->setRecordedOnFromDateTimeString(self::DATETIME);
    }

    /**
     * @test
     */
    public function it_updates_the_index_with_events_imported_from_udb2()
    {
        $this->userIdResolver->expects($this->once())
            ->method('resolveCreatedByToUserId')
            ->with('info@traeghe.be')
            ->willReturn(self::USER_ID);

        $this->repository->expects($this->once())
            ->method('updateIndex')
            ->with(
                '123-456',
                EntityType::EVENT(),
                self::USER_ID,
                'GALLERY TRAEGHE exhibition Janine de Coninck \'BLACK AND WHITE\' 1 - 9 November 2014',
                '8000',
                'BE',
                Domain::specifyType('udb.be'),
                new \DateTime(
                    '2014-09-08T09:10:16',
                    new \DateTimeZone('Europe/Brussels')
                )
            );

        $this->projector->handle(
            $this->domainMessageBuilder->create(
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
    public function it_ignores_events_imported_from_udb2_without_a_title()
    {
        $this->repository->expects($this->never())
            ->method('updateIndex');

        $msg = $this->domainMessageBuilder->create(
            new EventImportedFromUDB2(
                '123-456',
                file_get_contents(__DIR__ . '/event-with-empty-title.xml'),
                'http://www.cultuurdatabank.com/XMLSchema/CdbXSD/3.2/FINAL'
            )
        );

        $this->projector->handle($msg);
    }

    /**
     * @test
     */
    public function it_should_add_an_indexed_item_when_importing_a_place_from_udb2()
    {
        $this->userIdResolver->expects($this->once())
            ->method('resolveCreatedByToUserId')
            ->willReturn(self::USER_ID);

        $this->repository->expects($this->once())
            ->method('updateIndex')
            ->with(
                'place-lace-ace-ce',
                EntityType::PLACE(),
                self::USER_ID,
                'CC Palethe',
                '3900',
                'BE',
                Domain::specifyType('udb.be'),
                new \DateTime(
                    '2010-01-06T13:33:06+0100',
                    new \DateTimeZone('Europe/Brussels')
                )
            );

        $this->projector->handle(
            $this->domainMessageBuilder->create(
                new PlaceImportedFromUDB2(
                    'place-lace-ace-ce',
                    file_get_contents(__DIR__ . '/udb2_place.xml'),
                    'http://www.cultuurdatabank.com/XMLSchema/CdbXSD/3.2/FINAL'
                )
            )
        );
    }
}
