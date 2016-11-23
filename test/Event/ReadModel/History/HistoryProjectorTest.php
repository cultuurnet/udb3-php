<?php

namespace CultuurNet\UDB3\Event\ReadModel\History;

use Broadway\Domain\DateTime;
use Broadway\Domain\DomainMessage;
use Broadway\Domain\Metadata;
use CultuurNet\UDB3\CollaborationData;
use CultuurNet\UDB3\Event\Events\CollaborationDataAdded;
use CultuurNet\UDB3\Event\Events\DescriptionTranslated;
use CultuurNet\UDB3\Event\Events\EventCreatedFromCdbXml;
use CultuurNet\UDB3\Event\Events\EventImportedFromUDB2;
use CultuurNet\UDB3\Event\Events\EventUpdatedFromCdbXml;
use CultuurNet\UDB3\Event\Events\EventUpdatedFromUDB2;
use CultuurNet\UDB3\Event\Events\LabelAdded;
use CultuurNet\UDB3\Event\Events\LabelRemoved;
use CultuurNet\UDB3\Event\Events\LabelsMerged;
use CultuurNet\UDB3\Event\Events\TitleTranslated;
use CultuurNet\UDB3\Event\Events\TranslationApplied;
use CultuurNet\UDB3\Event\Events\TranslationDeleted;
use CultuurNet\UDB3\Event\ReadModel\DocumentRepositoryInterface;
use CultuurNet\UDB3\Event\ReadModel\InMemoryDocumentRepository;
use CultuurNet\UDB3\EventXmlString;
use CultuurNet\UDB3\Label;
use CultuurNet\UDB3\LabelCollection;
use CultuurNet\UDB3\Language;
use CultuurNet\UDB3\ReadModel\JsonDocument;
use ValueObjects\String\String;

class HistoryProjectorTest extends \PHPUnit_Framework_TestCase
{
    const EVENT_ID_1 = 'a0ee7b1c-a9c1-4da1-af7e-d15496014656';
    const EVENT_ID_2 = 'a2d50a8d-5b83-4c8b-84e6-e9c0bacbb1a3';

    const CDBXML_NAMESPACE = 'http://www.cultuurdatabank.com/XMLSchema/CdbXSD/3.2/FINAL';

    /**
     * @var HistoryProjector
     */
    protected $historyProjector;

    /**
     * @var DocumentRepositoryInterface
     */
    protected $documentRepository;

    public function setUp()
    {
        $this->documentRepository = new InMemoryDocumentRepository();

        $this->historyProjector = new HistoryProjector(
            $this->documentRepository
        );

        $eventImported = new EventImportedFromUDB2(
            self::EVENT_ID_1,
            $this->getEventCdbXml(self::EVENT_ID_1),
            self::CDBXML_NAMESPACE
        );

        $importedDate = '2015-03-04T10:17:19.176169+02:00';

        $domainMessage = new DomainMessage(
            $eventImported->getEventId(),
            1,
            new Metadata(),
            $eventImported,
            DateTime::fromString($importedDate)
        );

        $this->historyProjector->handle($domainMessage);
    }

    /**
     * @param string $eventId
     * @return string
     */
    protected function getEventCdbXml($eventId)
    {
        return file_get_contents(__DIR__ . '/event-' . $eventId . '.xml');
    }

    /**
     * @test
     */
    public function it_logs_EventImportedFromUDB2()
    {
        $this->assertHistoryOfEvent(
            self::EVENT_ID_1,
            [
                (object)[
                    'date' => '2015-03-04T10:17:19+02:00',
                    'description' => 'Geïmporteerd vanuit UDB2',
                ],
                (object)[
                    'date' => '2014-04-28T11:30:28+02:00',
                    'description' => 'Aangemaakt in UDB2',
                    'author' => 'kris.classen@overpelt.be',
                ]
            ]
        );

        $eventImported = new EventImportedFromUDB2(
            self::EVENT_ID_2,
            $this->getEventCdbXml(self::EVENT_ID_2),
            self::CDBXML_NAMESPACE
        );

        $importedDate = '2015-03-01T10:17:19.176169+02:00';

        $domainMessage = new DomainMessage(
            $eventImported->getEventId(),
            1,
            new Metadata(),
            $eventImported,
            DateTime::fromString($importedDate)
        );

        $this->historyProjector->handle($domainMessage);

        $this->assertHistoryOfEvent(
            self::EVENT_ID_2,
            [
                (object)[
                    'date' => '2015-03-01T10:17:19+02:00',
                    'description' => 'Geïmporteerd vanuit UDB2',
                ],
                (object)[
                    'date' => '2014-09-08T09:10:16+02:00',
                    'description' => 'Aangemaakt in UDB2',
                    'author' => 'info@traeghe.be',
                ]
            ]
        );
    }

    /**
     * @test
     */
    public function it_logs_EventUpdatedFromUDB2()
    {
        $eventUpdated = new EventUpdatedFromUDB2(
            self::EVENT_ID_1,
            $this->getEventCdbXml(self::EVENT_ID_1),
            self::CDBXML_NAMESPACE
        );

        $updatedDate = '2015-03-25T10:17:19.176169+02:00';

        $domainMessage = new DomainMessage(
            $eventUpdated->getEventId(),
            2,
            new Metadata(),
            $eventUpdated,
            DateTime::fromString($updatedDate)
        );

        $this->historyProjector->handle($domainMessage);

        $this->assertHistoryOfEvent(
            self::EVENT_ID_1,
            [
                (object)[
                    'description' => 'Geüpdatet vanuit UDB2',
                    'date' => '2015-03-25T10:17:19+02:00'
                ],
                (object)[
                    'date' => '2015-03-04T10:17:19+02:00',
                    'description' => 'Geïmporteerd vanuit UDB2',
                ],
                (object)[
                    'date' => '2014-04-28T11:30:28+02:00',
                    'description' => 'Aangemaakt in UDB2',
                    'author' => 'kris.classen@overpelt.be',
                ]
            ]
        );
    }

    /**
     * @test
     */
    public function it_logs_titleTranslated()
    {
        $titleTranslated = new TitleTranslated(
            self::EVENT_ID_1,
            new Language('fr'),
            new String('Titre en français')
        );

        $translatedDate = '2015-03-26T10:17:19.176169+02:00';

        $domainMessage = new DomainMessage(
            $titleTranslated->getItemId(),
            3,
            new Metadata(['user_nick' => 'JohnDoe']),
            $titleTranslated,
            DateTime::fromString($translatedDate)
        );

        $this->historyProjector->handle($domainMessage);

        $this->assertHistoryOfEvent(
            self::EVENT_ID_1,
            [
                (object)[
                    'date' => '2015-03-26T10:17:19+02:00',
                    'author' => 'JohnDoe',
                    'description' => 'Titel vertaald (fr)',
                ],
                (object)[
                    'date' => '2015-03-04T10:17:19+02:00',
                    'description' => 'Geïmporteerd vanuit UDB2',
                ],
                (object)[
                    'date' => '2014-04-28T11:30:28+02:00',
                    'description' => 'Aangemaakt in UDB2',
                    'author' => 'kris.classen@overpelt.be',
                ]
            ]
        );
    }

    /**
     * @test
     */
    public function it_logs_descriptionTranslated()
    {
        $descriptionTranslated = new DescriptionTranslated(
            self::EVENT_ID_1,
            new Language('fr'),
            new String('Signalement en français')
        );

        $translatedDate = '2015-03-27T10:17:19.176169+02:00';

        $domainMessage = new DomainMessage(
            $descriptionTranslated->getItemId(),
            3,
            new Metadata(['user_nick' => 'JaneDoe']),
            $descriptionTranslated,
            DateTime::fromString($translatedDate)
        );

        $this->historyProjector->handle($domainMessage);

        $this->assertHistoryOfEvent(
            self::EVENT_ID_1,
            [
                (object)[
                    'date' => '2015-03-27T10:17:19+02:00',
                    'author' => 'JaneDoe',
                    'description' => 'Beschrijving vertaald (fr)',
                ],
                (object)[
                    'date' => '2015-03-04T10:17:19+02:00',
                    'description' => 'Geïmporteerd vanuit UDB2',
                ],
                (object)[
                    'date' => '2014-04-28T11:30:28+02:00',
                    'description' => 'Aangemaakt in UDB2',
                    'author' => 'kris.classen@overpelt.be',
                ]
            ]
        );
    }

    /**
     * @test
     */
    public function it_logs_eventWasTagged()
    {
        $eventWasTagged = new LabelAdded(
            self::EVENT_ID_1,
            new Label('foo')
        );

        $taggedDate = '2015-03-27T10:17:19.176169+02:00';

        $domainMessage = new DomainMessage(
            $eventWasTagged->getItemId(),
            2,
            new Metadata(['user_nick' => 'Jan Janssen']),
            $eventWasTagged,
            DateTime::fromString($taggedDate)
        );

        $this->historyProjector->handle($domainMessage);

        $this->assertHistoryOfEvent(
            self::EVENT_ID_1,
            [
                (object)[
                    'date' => '2015-03-27T10:17:19+02:00',
                    'author' => 'Jan Janssen',
                    'description' => "Label 'foo' toegepast",
                ],
                (object)[
                    'date' => '2015-03-04T10:17:19+02:00',
                    'description' => 'Geïmporteerd vanuit UDB2',
                ],
                (object)[
                    'date' => '2014-04-28T11:30:28+02:00',
                    'description' => 'Aangemaakt in UDB2',
                    'author' => 'kris.classen@overpelt.be',
                ]
            ]
        );
    }

    /**
     * @test
     */
    public function it_logs_tagErased()
    {
        $tagErased = new LabelRemoved(
            self::EVENT_ID_1,
            new Label('foo')
        );

        $tagErasedDate = '2015-03-27T10:17:19.176169+02:00';

        $domainMessage = new DomainMessage(
            $tagErased->getItemId(),
            2,
            new Metadata(['user_nick' => 'Jan Janssen']),
            $tagErased,
            DateTime::fromString($tagErasedDate)
        );

        $this->historyProjector->handle($domainMessage);

        $this->assertHistoryOfEvent(
            self::EVENT_ID_1,
            [
                (object)[
                    'date' => '2015-03-27T10:17:19+02:00',
                    'author' => 'Jan Janssen',
                    'description' => "Label 'foo' verwijderd",
                ],
                (object)[
                    'date' => '2015-03-04T10:17:19+02:00',
                    'description' => 'Geïmporteerd vanuit UDB2',
                ],
                (object)[
                    'date' => '2014-04-28T11:30:28+02:00',
                    'description' => 'Aangemaakt in UDB2',
                    'author' => 'kris.classen@overpelt.be',
                ]
            ]
        );
    }

    /**
     * @test
     */
    public function it_logs_EventCreatedFromCdbXml()
    {
        $eventCreatedFromCdbXml = new EventCreatedFromCdbXml(
            new String(self::EVENT_ID_2),
            new EventXmlString($this->getEventCdbXml(self::EVENT_ID_2)),
            new String(self::CDBXML_NAMESPACE)
        );

        $importedDate = '2015-03-01T10:17:19.176169+02:00';

        $metadata = $this->entryApiMetadata('Jantest', 'UiTDatabank');

        $domainMessage = new DomainMessage(
            $eventCreatedFromCdbXml->getEventId()->toNative(),
            1,
            $metadata,
            $eventCreatedFromCdbXml,
            DateTime::fromString($importedDate)
        );

        $this->historyProjector->handle($domainMessage);

        $this->assertHistoryOfEvent(
            self::EVENT_ID_2,
            [
                (object)[
                    'date' => '2015-03-01T10:17:19+02:00',
                    'description' => 'Aangemaakt via EntryAPI door consumer "UiTDatabank"',
                    'author' => 'Jantest',
                ]
            ]
        );
    }

    /**
     * @test
     */
    public function it_logs_EventUpdatedFromCdbXml()
    {
        $eventUpdatedFromCdbXml = new EventUpdatedFromCdbXml(
            new String(self::EVENT_ID_2),
            new EventXmlString($this->getEventCdbXml(self::EVENT_ID_2)),
            new String(self::CDBXML_NAMESPACE)
        );

        $importedDate = '2015-03-01T10:17:19.176169+02:00';

        $metadata = $this->entryApiMetadata('Jantest', 'UiTDatabank');

        $domainMessage = new DomainMessage(
            $eventUpdatedFromCdbXml->getEventId()->toNative(),
            1,
            $metadata,
            $eventUpdatedFromCdbXml,
            DateTime::fromString($importedDate)
        );

        $this->historyProjector->handle($domainMessage);

        $this->assertHistoryOfEvent(
            self::EVENT_ID_2,
            [
                (object)[
                    'date' => '2015-03-01T10:17:19+02:00',
                    'description' => 'Geüpdatet via EntryAPI door consumer "UiTDatabank"',
                    'author' => 'Jantest',
                ]
            ]
        );
    }

    /**
     * @test
     */
    public function it_logs_LabelsMerged()
    {
        $labels = new LabelCollection(
            [
                new Label('label B', true),
                new Label('label C', false),
            ]
        );
        $labelsMerged = new LabelsMerged(
            new String(self::EVENT_ID_2),
            $labels
        );

        $importedDate = '2015-03-01T10:17:19.176169+02:00';

        $metadata = $this->entryApiMetadata('Jantest', 'UiTDatabank');

        $domainMessage = new DomainMessage(
            $labelsMerged->getEventId()->toNative(),
            1,
            $metadata,
            $labelsMerged,
            DateTime::fromString($importedDate)
        );

        $this->historyProjector->handle($domainMessage);

        $this->assertHistoryOfEvent(
            self::EVENT_ID_2,
            [
                (object)[
                    'date' => '2015-03-01T10:17:19+02:00',
                    'description' => "Labels 'label B', 'label C' toegepast via EntryAPI door consumer \"UiTDatabank\"",
                    'author' => 'Jantest',
                ]
            ]
        );
    }

    /**
     * @test
     */
    public function it_logs_TranslationApplied()
    {
        $translationApplied = new TranslationApplied(
            new String(self::EVENT_ID_2),
            new Language('en'),
            new String('Title'),
            new String('Short description'),
            new String('Long long long extra long description')
        );

        $importedDate = '2015-03-01T10:17:19.176169+02:00';

        $metadata = $this->entryApiMetadata('Jantest', 'UiTDatabank');

        $domainMessage = new DomainMessage(
            $translationApplied->getEventId()->toNative(),
            1,
            $metadata,
            $translationApplied,
            DateTime::fromString($importedDate)
        );

        $this->historyProjector->handle($domainMessage);

        $logMessage =
            'Titel, korte beschrijving, lange beschrijving vertaald (en) via EntryAPI door consumer "UiTDatabank"';

        $this->assertHistoryOfEvent(
            self::EVENT_ID_2,
            [
                (object)[
                    'date' => '2015-03-01T10:17:19+02:00',
                    'description' => $logMessage,
                    'author' => 'Jantest',
                ]
            ]
        );
    }

    /**
     * @test
     */
    public function it_logs_TranslationDeleted()
    {
        $translationDeleted = new TranslationDeleted(
            new String(self::EVENT_ID_2),
            new Language('en')
        );

        $importedDate = '2015-03-01T10:17:19.176169+02:00';

        $metadata = $this->entryApiMetadata('Jantest', 'UiTDatabank');

        $domainMessage = new DomainMessage(
            $translationDeleted->getEventId()->toNative(),
            1,
            $metadata,
            $translationDeleted,
            DateTime::fromString($importedDate)
        );

        $this->historyProjector->handle($domainMessage);

        $logMessage = 'Vertaling verwijderd (en) via EntryAPI door consumer "UiTDatabank"';

        $this->assertHistoryOfEvent(
            self::EVENT_ID_2,
            [
                (object)[
                    'date' => '2015-03-01T10:17:19+02:00',
                    'description' => $logMessage,
                    'author' => 'Jantest',
                ]
            ]
        );
    }

    /**
     * @test
     */
    public function it_logs_CollaborationDataAdded()
    {
        $collaborationDataAdded = new CollaborationDataAdded(
            new String(self::EVENT_ID_2),
            new Language('en'),
            new CollaborationData(
                new String('sub-brand-foo'),
                new String('plain text')
            )
        );

        $importedDate = '2015-03-01T10:17:19.176169+02:00';

        $metadata = $this->entryApiMetadata('Jantest', 'UiTDatabank');

        $domainMessage = new DomainMessage(
            $collaborationDataAdded->getEventId()->toNative(),
            1,
            $metadata,
            $collaborationDataAdded,
            DateTime::fromString($importedDate)
        );

        $this->historyProjector->handle($domainMessage);

        $logMessage = 'Collaboration data toegevoegd (en) voor sub brand "sub-brand-foo" via EntryAPI door consumer "UiTDatabank"';

        $this->assertHistoryOfEvent(
            self::EVENT_ID_2,
            [
                (object)[
                    'date' => '2015-03-01T10:17:19+02:00',
                    'description' => $logMessage,
                    'author' => 'Jantest',
                ]
            ]
        );
    }

    /**
     * @param string $eventId
     * @param array $history
     */
    protected function assertHistoryOfEvent($eventId, $history)
    {
        /** @var JsonDocument $document */
        $document = $this->documentRepository->get($eventId);

        $this->assertEquals(
            $history,
            $document->getBody()
        );
    }

    /**
     * @param string $userNick
     * @param string $consumerName
     * @return Metadata
     */
    protected function entryApiMetadata($userNick, $consumerName)
    {
        $values = [
            'user_nick' => $userNick,
            'consumer' => [
                'name' => $consumerName,
            ],
        ];

        return new Metadata($values);
    }
}
