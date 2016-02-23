<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\ReadModel\Index;

use Broadway\Domain\DateTime;
use Broadway\Domain\DomainMessage;
use Broadway\Domain\Metadata;
use CultuurNet\UDB3\Event\Events\EventImportedFromUDB2;
use CultuurNet\UDB3\Place\Events\PlaceImportedFromUDB2Event;
use Guzzle\Common\Event;

class ProjectorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var RepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $repository;

    /**
     * @var Projector
     */
    private $projector;

    const DATETIME = '2015-08-07T12:01:00.034024+00:00';

    public function setUp()
    {
        $this->repository = $this->getMock(RepositoryInterface::class);

        $this->projector = new Projector(
            $this->repository
        );
    }

    /**
     * @test
     */
    public function it_updates_the_index_with_events_imported_from_udb2()
    {
        $this->repository->expects($this->once())
            ->method('updateIndex')
            ->with(
                '123-456',
                EntityType::EVENT(),
                '',
                'GALLERY TRAEGHE exhibition Janine de Coninck \'BLACK AND WHITE\' 1 - 9 November 2014',
                '8000',
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
        $this->repository->expects($this->once())
            ->method('updateIndex')
            ->with(
                'place-lace-ace-ce',
                EntityType::PLACE(),
                '',
                'stuuuk',
                '3000',
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
