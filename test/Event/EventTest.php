<?php

namespace CultuurNet\UDB3\Event;

use Broadway\EventSourcing\Testing\AggregateRootScenarioTestCase;
use CultuurNet\UDB3\Address\Address;
use CultuurNet\UDB3\Address\Locality;
use CultuurNet\UDB3\Address\PostalCode;
use CultuurNet\UDB3\Address\Street;
use CultuurNet\UDB3\Calendar;
use CultuurNet\UDB3\CalendarType;
use CultuurNet\UDB3\ContactPoint;
use CultuurNet\UDB3\Event\Events\AudienceUpdated;
use CultuurNet\UDB3\Event\Events\Concluded;
use CultuurNet\UDB3\Event\Events\ContactPointUpdated;
use CultuurNet\UDB3\Event\Events\EventCopied;
use CultuurNet\UDB3\Event\Events\EventCreated;
use CultuurNet\UDB3\Event\Events\EventImportedFromUDB2;
use CultuurNet\UDB3\Event\Events\EventUpdatedFromUDB2;
use CultuurNet\UDB3\Event\Events\FacilitiesUpdated;
use CultuurNet\UDB3\Event\Events\ImageAdded;
use CultuurNet\UDB3\Event\Events\ImageRemoved;
use CultuurNet\UDB3\Event\Events\LabelAdded;
use CultuurNet\UDB3\Event\Events\LabelRemoved;
use CultuurNet\UDB3\Event\Events\LocationUpdated;
use CultuurNet\UDB3\Event\Events\Moderation\Published;
use CultuurNet\UDB3\Event\ValueObjects\Audience;
use CultuurNet\UDB3\Event\ValueObjects\AudienceType;
use CultuurNet\UDB3\Facility;
use CultuurNet\UDB3\Label;
use CultuurNet\UDB3\Language;
use CultuurNet\UDB3\Location\Location;
use CultuurNet\UDB3\Location\LocationId;
use CultuurNet\UDB3\Media\Image;
use CultuurNet\UDB3\Media\Properties\CopyrightHolder;
use CultuurNet\UDB3\Media\Properties\Description;
use CultuurNet\UDB3\Media\Properties\MIMEType;
use CultuurNet\UDB3\Title;
use RuntimeException;
use ValueObjects\Geography\Country;
use ValueObjects\Identity\UUID;
use ValueObjects\StringLiteral\StringLiteral;
use ValueObjects\Web\Url;

class EventTest extends AggregateRootScenarioTestCase
{
    const NS_CDBXML_3_2 = 'http://www.cultuurdatabank.com/XMLSchema/CdbXSD/3.2/FINAL';
    const NS_CDBXML_3_3 = 'http://www.cultuurdatabank.com/XMLSchema/CdbXSD/3.3/FINAL';

    /**
     * @inheritdoc
     */
    protected function getAggregateRootClass()
    {
        return Event::class;
    }

    /**
     * @var Event
     */
    protected $event;

    public function setUp()
    {
        parent::setUp();

        $this->event = Event::create(
            'foo',
            new Title('some representative title'),
            new EventType('0.50.4.0.0', 'concert'),
            new Location(
                UUID::generateAsString(),
                new StringLiteral('P-P-Partyzone'),
                new Address(
                    new Street('Kerkstraat 69'),
                    new PostalCode('3000'),
                    new Locality('Leuven'),
                    Country::fromNative('BE')
                )
            ),
            new Calendar(CalendarType::PERMANENT())
        );
    }

    private function getCreationEvent()
    {
        return new EventCreated(
            'd2b41f1d-598c-46af-a3a5-10e373faa6fe',
            new Title('some representative title'),
            new EventType('0.50.4.0.0', 'concert'),
            new Location(
                UUID::generateAsString(),
                new StringLiteral('P-P-Partyzone'),
                new Address(
                    new Street('Kerkstraat 69'),
                    new PostalCode('3000'),
                    new Locality('Leuven'),
                    Country::fromNative('BE')
                )
            ),
            new Calendar(CalendarType::PERMANENT())
        );
    }

    /**
     * @test
     */
    public function it_throws_an_error_when_creating_an_event_with_a_non_string_eventid()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected eventId to be a string, received integer');

        Event::create(
            101,
            new Title('some representative title'),
            new EventType('0.50.4.0.0', 'concert'),
            new Location(
                UUID::generateAsString(),
                new StringLiteral('P-P-Partyzone'),
                new Address(
                    new Street('Kerkstraat 69'),
                    new PostalCode('3000'),
                    new Locality('Leuven'),
                    Country::fromNative('BE')
                )
            ),
            new Calendar(CalendarType::PERMANENT())
        );
    }

    /**
     * @test
     * @group issue-III-1380
     */
    public function it_handles_copy_event()
    {
        $newEventId = 'e49430ca-5729-4768-8364-02ddb385517a';
        $calendar = new Calendar(
            CalendarType::SINGLE(),
            new \DateTime()
        );

        $event = $this->event;

        $this->event->getUncommittedEvents();

        $this->scenario
            ->when(function () use ($event, $newEventId, $calendar) {
                return $event->copy(
                    $newEventId,
                    $calendar
                );
            })
            ->then(
                [
                    new EventCopied(
                        $newEventId,
                        'foo',
                        $calendar
                    ),
                ]
            );
    }

    /**
     * @test
     */
    public function it_handles_update_facilities_after_udb2_update()
    {
        $eventId = 'd2b41f1d-598c-46af-a3a5-10e373faa6fe';
        $createEvent = $this->getCreationEvent();

        $facilities = [
            new Facility("3.27.0.0.0", "Rolstoeltoegankelijk"),
            new Facility("3.30.0.0.0", "Rolstoelpodium"),
        ];

        $xmlData = $this->getSample('EventTest.cdbxml.xml');
        $xmlNamespace = self::NS_CDBXML_3_2;

        $this->scenario
            ->given(
                [
                    $createEvent,
                    new FacilitiesUpdated($eventId, $facilities),
                    new EventUpdatedFromUDB2($eventId, $xmlData, $xmlNamespace),
                ]
            )
            ->when(
                function (Event $event) use ($facilities) {
                    $event->updateFacilities($facilities);
                }
            )
            ->then(
                [
                    new FacilitiesUpdated($eventId, $facilities),
                ]
            );
    }

    /**
     * @test
     */
    public function it_handles_update_contact_point_after_udb2_import()
    {
        $eventId = 'd2b41f1d-598c-46af-a3a5-10e373faa6fe';
        $createEvent = $this->getCreationEvent();

        $contactPoint = new ContactPoint(
            ['016/101010',],
            ['test@2dotstwice.be', 'admin@2dotstwice.be'],
            ['http://www.2dotstwice.be']
        );

        $xmlData = $this->getSample('EventTest.cdbxml.xml');
        $xmlNamespace = self::NS_CDBXML_3_2;

        $this->scenario
            ->given(
                [
                    $createEvent,
                    new ContactPointUpdated($eventId, $contactPoint),
                    new EventUpdatedFromUDB2($eventId, $xmlData, $xmlNamespace),
                ]
            )
            ->when(
                function (Event $event) use ($contactPoint) {
                    $event->updateContactPoint($contactPoint);
                }
            )
            ->then(
                [
                    new ContactPointUpdated($eventId, $contactPoint),
                ]
            );
    }

    /**
     * @test
     */
    public function it_can_be_tagged_with_multiple_labels()
    {
        $this->scenario
            ->given([
                $this->getCreationEvent(),
            ])
            ->when(
                function (Event $event) {
                    $event->addLabel(new Label('foo'));
                    $event->addLabel(new Label('bar'));
                }
            )
            ->then([
                new LabelAdded('d2b41f1d-598c-46af-a3a5-10e373faa6fe', new Label('foo')),
                new LabelAdded('d2b41f1d-598c-46af-a3a5-10e373faa6fe', new Label('bar')),
            ]);
    }

    /**
     * @test
     */
    public function it_only_applies_the_same_tag_once()
    {
        $this->scenario
            ->given([
                $this->getCreationEvent(),
            ])
            ->when(
                function (Event $event) {
                    $event->addLabel(new Label('foo'));
                    $event->addLabel(new Label('foo'));
                }
            )
            ->then([
                new LabelAdded('d2b41f1d-598c-46af-a3a5-10e373faa6fe', new Label('foo')),
            ]);
    }

    /**
     * @test
     */
    public function it_does_not_add_similar_labels_with_different_letter_casing()
    {
        $this->scenario
            ->given([
                $this->getCreationEvent(),
            ])
            ->when(
                function (Event $event) {
                    $event->addLabel(new Label('Foo'));
                    $event->addLabel(new Label('foo'));
                    $event->addLabel(new Label('België'));
                    $event->addLabel(new Label('BelgiË'));
                }
            )
            ->then([
                new LabelAdded('d2b41f1d-598c-46af-a3a5-10e373faa6fe', new Label('Foo')),
                new LabelAdded('d2b41f1d-598c-46af-a3a5-10e373faa6fe', new Label('België')),
            ]);
    }

    /**
     * @test
     */
    public function it_can_be_imported_from_udb2_cdbxml_without_any_labels()
    {
        $xmlData = $this->getSample('EventTest.cdbxml.xml');
        $eventId = 'a2d50a8d-5b83-4c8b-84e6-e9c0bacbb1a3';
        $xmlNamespace = self::NS_CDBXML_3_2;

        $this->scenario
            ->given([
                new EventImportedFromUDB2($eventId, $xmlData, $xmlNamespace),
            ])
            ->when(
                function (Event $event) {
                    $event->addLabel(new Label('kunst'));
                    $event->addLabel(new Label('tentoonstelling'));
                    $event->addLabel(new Label('brugge'));
                    $event->addLabel(new Label('grafiek'));
                    $event->addLabel(new Label('oud sint jan'));
                    $event->addLabel(new Label('TRAEGHE GENUINE ARTS'));
                    $event->addLabel(new Label('janine de conink'));
                    $event->addLabel(new Label('brugge oktober'));
                }
            )
            ->then([]);
    }

    /**
     * @return array
     */
    public function unlabelDataProvider()
    {
        $label = new Label('foo');

        $id = '004aea08-e13d-48c9-b9eb-a18f20e6d44e';
        $ns = self::NS_CDBXML_3_3;
        $cdbXml = $this->getSample('event_004aea08-e13d-48c9-b9eb-a18f20e6d44e.xml');
        $cdbXmlWithFooKeyword = $this->getSample('event_004aea08-e13d-48c9-b9eb-a18f20e6d44e_additional_keyword.xml');

        $eventImportedFromUdb2 = new EventImportedFromUDB2(
            $id,
            $cdbXml,
            $ns
        );

        return [
            'label added by udb3' => [
                $id,
                $label,
                [
                    $eventImportedFromUdb2,
                    new LabelAdded(
                        $id,
                        $label
                    ),
                ],
            ],
            'label added by update from udb2' => [
                $id,
                $label,
                [
                    $eventImportedFromUdb2,
                    new EventUpdatedFromUDB2(
                        $id,
                        $cdbXmlWithFooKeyword,
                        $ns
                    ),
                ],
            ],
            'label with different casing' => [
                $id,
                $label,
                [
                    $eventImportedFromUdb2,
                    new LabelAdded(
                        $id,
                        new Label('fOO')
                    ),
                ],
            ],
        ];
    }

    /**
     * @test
     * @dataProvider unlabelDataProvider
     * @param string $id
     * @param Label $label
     * @param array $givens
     */
    public function it_can_be_unlabelled(
        $id,
        Label $label,
        array $givens
    ) {
        $this->scenario
            ->given($givens)
            ->when(
                function (Event $event) use ($label) {
                    $event->removeLabel($label);
                }
            )
            ->then(
                [
                    new LabelRemoved($id, $label),
                ]
            );
    }

    /**
     * @return array
     */
    public function unlabelIgnoredDataProvider()
    {
        $label = new Label('foo');

        $id = '004aea08-e13d-48c9-b9eb-a18f20e6d44e';
        $ns = self::NS_CDBXML_3_3;
        $cdbXml = $this->getSample('event_004aea08-e13d-48c9-b9eb-a18f20e6d44e.xml');
        $cdbXmlWithFooKeyword = $this->getSample('event_004aea08-e13d-48c9-b9eb-a18f20e6d44e_additional_keyword.xml');

        $eventImportedFromUdb2 = new EventImportedFromUDB2(
            $id,
            $cdbXml,
            $ns
        );

        return [
            'label not present in imported udb2 cdbxml' => [
                $label,
                [
                    $eventImportedFromUdb2,
                ],
            ],
            'label previously removed by an update from udb2' => [
                $label,
                [
                    new EventImportedFromUDB2(
                        $id,
                        $cdbXmlWithFooKeyword,
                        $ns
                    ),
                    new EventUpdatedFromUDB2(
                        $id,
                        $cdbXml,
                        $ns
                    ),
                ],
            ],
            'label previously removed' => [
                $label,
                [
                    $eventImportedFromUdb2,
                    new LabelAdded(
                        $id,
                        $label
                    ),
                    new LabelRemoved(
                        $id,
                        $label
                    ),
                ],
            ],
        ];
    }

    /**
     * @test
     * @dataProvider unlabelIgnoredDataProvider
     * @param Label $label
     * @param array $givens
     */
    public function it_silently_ignores_unlabel_request_if_label_is_not_present(
        Label $label,
        array $givens
    ) {
        $this->scenario
            ->given($givens)
            ->when(
                function (Event $event) use ($label) {
                    $event->removeLabel($label);
                }
            )
            ->then([]);
    }

    /**
     * @test
     */
    public function it_should_not_add_duplicate_images()
    {
        $image = new Image(
            new UUID('de305d54-75b4-431b-adb2-eb6b9e546014'),
            new MIMEType('image/png'),
            new Description('sexy ladies without clothes'),
            new CopyrightHolder('Bart Ramakers'),
            Url::fromNative('http://foo.bar/media/de305d54-75b4-431b-adb2-eb6b9e546014.png'),
            new Language('en')
        );

        $cdbXml = file_get_contents(
            __DIR__ . '/samples/event_entryapi_valid_with_keywords.xml'
        );

        $this->scenario
            ->withAggregateId('004aea08-e13d-48c9-b9eb-a18f20e6d44e')
            ->given(
                [
                    new EventImportedFromUDB2(
                        '004aea08-e13d-48c9-b9eb-a18f20e6d44e',
                        $cdbXml,
                        \CultureFeed_Cdb_Xml::namespaceUriForVersion('3.3')
                    ),
                    new ImageAdded(
                        '004aea08-e13d-48c9-b9eb-a18f20e6d44e',
                        $image
                    ),
                ]
            )
            ->when(
                function (Event $event) use ($image) {
                    $event->addImage(
                        $image
                    );
                }
            )
            ->then([]);
    }

    /**
     * @test
     */
    public function it_removes_images()
    {
        $cdbXml = file_get_contents(
            __DIR__ . '/samples/event_entryapi_valid_with_keywords.xml'
        );

        $image = new Image(
            new UUID('de305d54-75b4-431b-adb2-eb6b9e546014'),
            new MIMEType('image/png'),
            new Description('sexy ladies without clothes'),
            new CopyrightHolder('Bart Ramakers'),
            Url::fromNative('http://foo.bar/media/de305d54-75b4-431b-adb2-eb6b9e546014.png'),
            new Language('en')
        );

        $this->scenario
            ->withAggregateId('004aea08-e13d-48c9-b9eb-a18f20e6d44e')
            ->given(
                [
                    new EventImportedFromUDB2(
                        '004aea08-e13d-48c9-b9eb-a18f20e6d44e',
                        $cdbXml,
                        \CultureFeed_Cdb_Xml::namespaceUriForVersion('3.3')
                    ),
                    new ImageAdded(
                        '004aea08-e13d-48c9-b9eb-a18f20e6d44e',
                        $image
                    ),
                ]
            )
            ->when(
                function (Event $event) use ($image) {
                    $event->removeImage(
                        $image
                    );
                }
            )
            ->then(
                [
                    new ImageRemoved(
                        '004aea08-e13d-48c9-b9eb-a18f20e6d44e',
                        $image
                    ),
                ]
            );
    }

    /**
     * @test
     */
    public function it_silently_ignores_an_image_removal_request_when_image_is_not_present()
    {
        $cdbXml = file_get_contents(
            __DIR__ . '/samples/event_entryapi_valid_with_keywords.xml'
        );

        $image = new Image(
            new UUID('de305d54-75b4-431b-adb2-eb6b9e546014'),
            new MIMEType('image/png'),
            new Description('sexy ladies without clothes'),
            new CopyrightHolder('Bart Ramakers'),
            Url::fromNative('http://foo.bar/media/de305d54-75b4-431b-adb2-eb6b9e546014.png'),
            new Language('en')
        );

        $this->scenario
            ->withAggregateId('004aea08-e13d-48c9-b9eb-a18f20e6d44e')
            ->given(
                [
                    new EventImportedFromUDB2(
                        '004aea08-e13d-48c9-b9eb-a18f20e6d44e',
                        $cdbXml,
                        \CultureFeed_Cdb_Xml::namespaceUriForVersion('3.3')
                    ),
                ]
            )
            ->when(
                function (Event $event) use ($image) {
                    $event->removeImage(
                        $image
                    );
                }
            )
            ->then([]);
    }

    /**
     * @test
     */
    public function it_handles_update_location()
    {
        $eventId = 'd2b41f1d-598c-46af-a3a5-10e373faa6fe';
        $createEvent = $this->getCreationEvent();
        $oldLocationId = new LocationId($createEvent->getLocation()->getCdbid());
        $newLocationId = new LocationId('57738178-28a5-4afb-90c0-fd0beba172a8');

        $this->scenario
            ->given(
                [
                    $createEvent,
                ]
            )
            ->when(
                function (Event $event) use ($oldLocationId, $newLocationId) {
                    $event->updateLocation($oldLocationId);
                    $event->updateLocation($newLocationId);
                    $event->updateLocation($newLocationId);
                }
            )
            ->then(
                [
                    new LocationUpdated($eventId, $newLocationId),
                ]
            );
    }

    /**
     * @test
     */
    public function it_handles_update_location_after_udb2_import()
    {
        $eventId = 'd2b41f1d-598c-46af-a3a5-10e373faa6fe';
        $createEvent = $this->getCreationEvent();
        $locationId = new LocationId($createEvent->getLocation()->getCdbid());

        $xmlData = $this->getSample('EventTest.cdbxml.xml');
        $xmlNamespace = self::NS_CDBXML_3_2;

        $this->scenario
            ->given(
                [
                    $createEvent,
                    new LocationUpdated($eventId, $locationId),
                    new EventImportedFromUDB2($eventId, $xmlData, $xmlNamespace),
                ]
            )
            ->when(
                function (Event $event) use ($locationId) {
                    $event->updateLocation($locationId);
                }
            )
            ->then(
                [
                    new LocationUpdated($eventId, $locationId),
                ]
            );
    }

    /**
     * @test
     * @dataProvider audienceDataProvider
     * @param Audience[] $audiences
     * @param AudienceUpdated[] $audienceUpdatedEvents
     */
    public function it_applies_the_audience_type(
        $audiences,
        $audienceUpdatedEvents
    ) {
        $this->scenario
            ->given([
                $this->getCreationEvent(),
            ])
            ->when(
                function (Event $event) use ($audiences) {
                    foreach ($audiences as $audience) {
                        $event->updateAudience($audience);
                    }
                }
            )
            ->then(
                $audienceUpdatedEvents
            );
    }

    /**
     * @return array
     */
    public function audienceDataProvider()
    {
        $eventId = 'd2b41f1d-598c-46af-a3a5-10e373faa6fe';

        return [
            'single audience type' =>
                [
                    [
                        new Audience(AudienceType::MEMBERS()),
                    ],
                    [
                        new AudienceUpdated(
                            $eventId,
                            new Audience(AudienceType::MEMBERS())
                        ),
                    ],
                ],
            'multiple audience types' =>
                [
                    [
                        new Audience(AudienceType::MEMBERS()),
                        new Audience(AudienceType::EVERYONE()),
                    ],
                    [
                        new AudienceUpdated(
                            $eventId,
                            new Audience(AudienceType::MEMBERS())
                        ),
                        new AudienceUpdated(
                            $eventId,
                            new Audience(AudienceType::EVERYONE())
                        ),
                    ],
                ],
            'equal audience types' =>
                [
                    [
                        new Audience(AudienceType::MEMBERS()),
                        new Audience(AudienceType::MEMBERS()),
                    ],
                    [
                        new AudienceUpdated(
                            $eventId,
                            new Audience(AudienceType::MEMBERS())
                        ),
                    ],
                ],
        ];
    }

    /**
     * @test
     * @group issue-III-1380
     */
    public function it_refuses_to_copy_when_there_are_uncommitted_events()
    {
        $event = $this->event;

        $this->expectException(RuntimeException::class);

        $event->copy(
            'e49430ca-5729-4768-8364-02ddb385517a',
            new Calendar(
                CalendarType::SINGLE(),
                new \DateTime()
            )
        );
    }

    /**
     * @test
     * @group issue-III-1380
     */
    public function it_resets_labels_on_copy()
    {
        $newEventId = 'e49430ca-5729-4768-8364-02ddb385517a';
        $calendar = new Calendar(
            CalendarType::SINGLE(),
            new \DateTime()
        );
        $label = new Label('ABC');

        $event = $this->event;
        $event->addLabel($label);

        $event->getUncommittedEvents();

        $this->scenario
            ->when(function () use ($event, $newEventId, $calendar, $label) {
                $newEvent = $event->copy(
                    $newEventId,
                    $calendar
                );

                $newEvent->addLabel($label);

                return $newEvent;
            })
            ->then(
                [
                    new EventCopied(
                        $newEventId,
                        'foo',
                        $calendar
                    ),
                    new LabelAdded(
                        $newEventId,
                        $label
                    ),
                ]
            );
    }

    /**
     * @test
     * @group issue-III-1380
     */
    public function it_keeps_audience_on_copy()
    {
        $newEventId = 'e49430ca-5729-4768-8364-02ddb385517a';
        $calendar = new Calendar(
            CalendarType::SINGLE(),
            new \DateTime()
        );
        $audience = new Audience(AudienceType::EDUCATION());

        $event = $this->event;
        $event->updateAudience($audience);

        $event->getUncommittedEvents();

        $this->scenario
            ->when(function () use ($event, $newEventId, $calendar, $audience) {
                $newEvent = $event->copy(
                    $newEventId,
                    $calendar
                );

                $newEvent->updateAudience($audience);

                return $newEvent;
            })
            ->then(
                [
                    new EventCopied(
                        $newEventId,
                        'foo',
                        $calendar
                    ),
                ]
            );
    }

    /**
     * @test
     * @group issue-III-1380
     */
    public function it_resets_workflow_status_on_copy()
    {
        $newEventId = 'e49430ca-5729-4768-8364-02ddb385517a';
        $calendar = new Calendar(
            CalendarType::SINGLE(),
            new \DateTime()
        );

        $publicationDate = new \DateTimeImmutable();

        $event = $this->event;
        $event->publish($publicationDate);

        $event->getUncommittedEvents();

        $newPublicationDate = new \DateTimeImmutable("+3 days");

        $this->scenario
            ->when(function () use ($event, $newEventId, $calendar, $newPublicationDate) {
                $newEvent = $event->copy(
                    $newEventId,
                    $calendar
                );

                $newEvent->publish($newPublicationDate);

                return $newEvent;
            })
            ->then(
                [
                    new EventCopied(
                        $newEventId,
                        'foo',
                        $calendar
                    ),
                    new Published(
                        $newEventId,
                        $newPublicationDate
                    ),
                ]
            );
    }

    /**
     * @test
     * @group issue-III-1807
     */
    public function it_can_be_concluded()
    {
        $this->scenario
            ->withAggregateId('d2b41f1d-598c-46af-a3a5-10e373faa6fe')
            ->given([
                $this->getCreationEvent(),
            ])
            ->when(
                function (Event $event) {
                    $event->conclude();
                }
            )
            ->then(
                [new Concluded('d2b41f1d-598c-46af-a3a5-10e373faa6fe')]
            )
            ->when(
                function (Event $event) {
                    $event->conclude();
                }
            )
            ->then([]);
    }

    /**
     * @param string $file
     * @return string
     */
    protected function getSample($file)
    {
        return file_get_contents(
            __DIR__ . '/samples/' . $file
        );
    }
}
