<?php

namespace CultuurNet\UDB3\ReadModel\Index;

use Broadway\Domain\DateTime;
use Broadway\Domain\DomainMessage;
use Broadway\Domain\Metadata;
use CultuurNet\UDB3\Address\Address;
use CultuurNet\UDB3\Address\Locality;
use CultuurNet\UDB3\Address\PostalCode;
use CultuurNet\UDB3\Address\Street;
use CultuurNet\UDB3\Calendar;
use CultuurNet\UDB3\CalendarType;
use CultuurNet\UDB3\Cdb\CreatedByToUserIdResolverInterface;
use CultuurNet\UDB3\Event\Events\EventCopied;
use CultuurNet\UDB3\Event\Events\EventCreated;
use CultuurNet\UDB3\Event\Events\EventDeleted;
use CultuurNet\UDB3\Event\Events\EventImportedFromUDB2;
use CultuurNet\UDB3\Event\Events\EventProjectedToJSONLD;
use CultuurNet\UDB3\Event\EventType;
use CultuurNet\UDB3\Location\Location;
use CultuurNet\UDB3\Offer\IriOfferIdentifier;
use CultuurNet\UDB3\Offer\IriOfferIdentifierFactoryInterface;
use CultuurNet\UDB3\Offer\OfferType;
use CultuurNet\UDB3\Place\Events\PlaceCreated;
use CultuurNet\UDB3\Place\Events\PlaceDeleted;
use CultuurNet\UDB3\Place\Events\PlaceImportedFromUDB2;
use CultuurNet\UDB3\Place\Events\PlaceProjectedToJSONLD;
use CultuurNet\UDB3\Theme;
use CultuurNet\UDB3\Title;
use ValueObjects\Geography\Country;
use ValueObjects\Web\Domain;
use ValueObjects\Web\Url;
use ValueObjects\StringLiteral\StringLiteral;

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

    /**
     * @var IriOfferIdentifierFactoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $iriOfferIdentifierFactory;

    const DATETIME = '2015-08-07T12:01:00.034024+00:00';

    public function setUp()
    {
        $this->repository = $this->createMock(RepositoryInterface::class);
        $this->userIdResolver = $this->createMock(CreatedByToUserIdResolverInterface::class);
        $this->iriOfferIdentifierFactory = $this->createMock(IriOfferIdentifierFactoryInterface::class);

        $this->projector = new Projector(
            $this->repository,
            $this->userIdResolver,
            Domain::specifyType('omd.be'),
            Domain::specifyType('udb.be'),
            $this->iriOfferIdentifierFactory
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
    public function it_should_add_an_indexed_item_when_an_event_is_created()
    {
        $this->repository->expects($this->once())
            ->method('updateIndex')
            ->with(
                'f2b227c5-4756-49f6-a25d-8286b6a2351f',
                EntityType::EVENT(),
                '1adf21b4-711d-4e33-b9ef-c96843582a56',
                'Algorave',
                '9630',
                Domain::specifyType('omd.be'),
                $this->anything()
            );

        $this->projector->handle(
            $this->domainMessage(
                new EventCreated(
                    'f2b227c5-4756-49f6-a25d-8286b6a2351f',
                    new Title('Algorave'),
                    new EventType('0.50.4.0.0', 'concert'),
                    new Location(
                        '335be568-aaf0-4147-80b6-9267daafe23b',
                        new StringLiteral('Repeteerkot'),
                        new Address(
                            new Street('Kerkstraat 69'),
                            new PostalCode('9630'),
                            new Locality('Zottegem'),
                            Country::fromNative('BE')
                        )
                    ),
                    new Calendar(CalendarType::PERMANENT()),
                    new Theme('themeid', 'theme_label')
                )
            )
        );
    }

    /**
     * @test
     */
    public function it_adds_an_index_when_event_is_copied()
    {
        $eventId = 'f2b227c5-4756-49f6-a25d-8286b6a2351f';
        $originalEventId = '1fd05542-ce0b-4ed1-ad17-cf5a0f316da4';
        $userId = '1adf21b4-711d-4e33-b9ef-c96843582a56';

        $eventCopied = new EventCopied(
            $eventId,
            $originalEventId,
            new Calendar(CalendarType::PERMANENT())
        );

        $domainMessage = $this->domainMessage($eventCopied);

        $this->repository->expects($this->once())
            ->method('updateIndex')
            ->with(
                $eventId,
                EntityType::EVENT(),
                $userId,
                null,
                null,
                Domain::specifyType('omd.be'),
                $this->isInstanceOf(\DateTime::class)
            );

        $this->projector->handle($domainMessage);
    }

    /**
     * @test
     */
    public function it_should_add_an_indexed_item_when_a_place_is_created()
    {
        $this->repository->expects($this->once())
            ->method('updateIndex')
            ->with(
                'f2b227c5-4756-49f6-a25d-8286b6a2351f',
                EntityType::PLACE(),
                '1adf21b4-711d-4e33-b9ef-c96843582a56',
                'Algorave',
                '9630',
                Domain::specifyType('omd.be'),
                $this->anything()
            );

        $this->projector->handle(
            $this->domainMessage(
                new PlaceCreated(
                    'f2b227c5-4756-49f6-a25d-8286b6a2351f',
                    new Title('Algorave'),
                    new EventType('0.50.4.0.0', 'concert'),
                    new Address(
                        new Street('Kerkstraat 69'),
                        new PostalCode('9630'),
                        new Locality('Zottegem'),
                        Country::fromNative('BE')
                    ),
                    new Calendar(CalendarType::PERMANENT()),
                    new Theme('themeid', 'theme_label')
                )
            )
        );
    }

    /**
     * @test
     * @dataProvider indexUpdateEventsDataProvider
     *
     * @param DomainMessage $domainMessage
     * @param string $itemId
     */
    public function it_should_set_the_update_date_when_indexed_items_change(
        DomainMessage $domainMessage,
        $itemId
    ) {
        $this->iriOfferIdentifierFactory
            ->method('fromIri')
            ->willReturn(new IriOfferIdentifier(
                Url::fromNative('http://du.de/1'),
                $itemId,
                OfferType::EVENT()
            ));

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
                        'http://du.de/place/6ecf5da4-220d-4486-9327-17c7ec8fa070'
                    )
                ),
                '6ecf5da4-220d-4486-9327-17c7ec8fa070'
            ),
            array(
                $this->domainMessage(
                    new EventProjectedToJSONLD(
                        'http://du.de/event/6ecf5da4-220d-4486-9327-17c7ec8fa070'
                    )
                ),
                '6ecf5da4-220d-4486-9327-17c7ec8fa070'
            ),
        );
    }

    /**
     * @test
     */
    public function it_should_remove_the_index_of_deleted_events()
    {
        $this->repository->expects($this->once())
            ->method('deleteIndex')
            ->with('6ecf5da4-220d-4486-9327-17c7ec8fa070');

        $this->projector->handle(
            $this->domainMessage(
                new EventDeleted('6ecf5da4-220d-4486-9327-17c7ec8fa070')
            )
        );
    }

    /**
     * @test
     */
    public function it_should_remove_the_index_of_deleted_places()
    {
        $this->repository->expects($this->once())
            ->method('deleteIndex')
            ->with('6ecf5da4-220d-4486-9327-17c7ec8fa070');

        $this->projector->handle(
            $this->domainMessage(
                new PlaceDeleted('6ecf5da4-220d-4486-9327-17c7ec8fa070')
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
            new Metadata([
                'user_id' => '1adf21b4-711d-4e33-b9ef-c96843582a56'
            ]),
            $payload,
            DateTime::fromString(self::DATETIME)
        );
    }
}
