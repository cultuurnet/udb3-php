<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Event\ReadModel\History;

use Broadway\Domain\DateTime;
use Broadway\Domain\DomainMessage;
use Broadway\Domain\Metadata;
use CultuurNet\UDB3\Event\EventImportedFromUDB2;
use CultuurNet\UDB3\Event\Events\EventUpdatedFromUDB2;
use CultuurNet\UDB3\Event\ReadModel\DocumentRepositoryInterface;
use CultuurNet\UDB3\Event\ReadModel\InMemoryDocumentRepository;
use CultuurNet\UDB3\Event\ReadModel\JsonDocument;
use DateTime as BaseDateTime;

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
                    'description' => 'Updatet vanuit UDB2',
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
}
