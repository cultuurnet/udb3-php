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
use CultuurNet\UDB3\Event\Events\EventProjectedToJSONLD;
use CultuurNet\UDB3\Event\EventType;
use CultuurNet\UDB3\EventSourcing\DomainMessageBuilder;
use CultuurNet\UDB3\Language;
use CultuurNet\UDB3\Location\Location;
use CultuurNet\UDB3\Offer\IriOfferIdentifier;
use CultuurNet\UDB3\Offer\IriOfferIdentifierFactoryInterface;
use CultuurNet\UDB3\Offer\OfferType;
use CultuurNet\UDB3\Place\Events\PlaceCreated;
use CultuurNet\UDB3\Place\Events\PlaceDeleted;
use CultuurNet\UDB3\Place\Events\PlaceProjectedToJSONLD;
use CultuurNet\UDB3\Theme;
use CultuurNet\UDB3\Title;
use ValueObjects\Geography\Country;
use ValueObjects\StringLiteral\StringLiteral;
use ValueObjects\Web\Domain;
use ValueObjects\Web\Url;

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

    /**
     * @var DomainMessageBuilder
     */
    private $domainMessageBuilder;

    private const DATETIME = '2015-08-07T12:01:00.034024+00:00';

    private const USER_ID = '1adf21b4-711d-4e33-b9ef-c96843582a56';

    public function setUp()
    {
        $this->repository = $this->createMock(RepositoryInterface::class);

        $this->iriOfferIdentifierFactory = $this->createMock(IriOfferIdentifierFactoryInterface::class);

        $this->projector = new Projector(
            $this->repository,
            Domain::specifyType('omd.be'),
            $this->iriOfferIdentifierFactory
        );

        $this->domainMessageBuilder = (new DomainMessageBuilder())
            ->setUserId(self::USER_ID)
            ->setRecordedOnFromDateTimeString(self::DATETIME);
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
                'Zottegem',
                'BE',
                Domain::specifyType('omd.be'),
                $this->anything()
            );

        $this->projector->handle(
            $this->domainMessageBuilder->create(
                new EventCreated(
                    'f2b227c5-4756-49f6-a25d-8286b6a2351f',
                    new Language('en'),
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

        $eventCopied = new EventCopied(
            $eventId,
            $originalEventId,
            new Calendar(CalendarType::PERMANENT())
        );

        $domainMessage = $this->domainMessageBuilder->create($eventCopied);

        $this->repository->expects($this->once())
            ->method('updateIndex')
            ->with(
                $eventId,
                EntityType::EVENT(),
                self::USER_ID,
                '',
                '',
                '',
                '',
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
                'Zottegem',
                'BE',
                Domain::specifyType('omd.be'),
                $this->anything()
            );

        $this->projector->handle(
            $this->domainMessageBuilder->create(
                new PlaceCreated(
                    'f2b227c5-4756-49f6-a25d-8286b6a2351f',
                    new Language('en'),
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
     * @param object $event
     * @param string $itemId
     */
    public function it_should_set_the_update_date_when_indexed_items_change(
        $event,
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

        $this->projector->handle(
            $this->domainMessageBuilder->create($event)
        );
    }

    public function indexUpdateEventsDataProvider()
    {
        return array(
            array(
                new PlaceProjectedToJSONLD(
                    '6ecf5da4-220d-4486-9327-17c7ec8fa070',
                    'http://du.de/place/6ecf5da4-220d-4486-9327-17c7ec8fa070'
                ),
                '6ecf5da4-220d-4486-9327-17c7ec8fa070',
            ),
            array(
                new EventProjectedToJSONLD(
                    '6ecf5da4-220d-4486-9327-17c7ec8fa070',
                    'http://du.de/event/6ecf5da4-220d-4486-9327-17c7ec8fa070'
                ),
                '6ecf5da4-220d-4486-9327-17c7ec8fa070',
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
            $this->domainMessageBuilder->create(
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
            $this->domainMessageBuilder->create(
                new PlaceDeleted('6ecf5da4-220d-4486-9327-17c7ec8fa070')
            )
        );
    }
}
