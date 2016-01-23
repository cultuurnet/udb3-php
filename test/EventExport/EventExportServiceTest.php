<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\EventExport;

use Broadway\UuidGenerator\UuidGeneratorInterface;
use CultuurNet\UDB3\EventExport\Notification\NotificationMailerInterface;
use CultuurNet\UDB3\EventServiceInterface;
use CultuurNet\UDB3\Iri\IriGeneratorInterface;
use CultuurNet\UDB3\Search\Results;
use CultuurNet\UDB3\Search\SearchServiceInterface;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use org\bovigo\vfs\vfsStreamFile;
use PHPUnit_Framework_TestCase;
use Psr\Log\LoggerInterface;
use ValueObjects\Number\Integer;

class EventExportServiceTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var EventExportService
     */
    protected $eventExportService;

    /**
     * @var EventServiceInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $eventService;

    /**
     * @var SearchServiceInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $searchService;

    /**
     * @var UuidGeneratorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $uuidGenerator;

    /**
     * @var IriGeneratorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $iriGenerator;

    /**
     * @var NotificationMailerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $mailer;

    /**
     * @var vfsStreamDirectory
     */
    protected $publicDirectory;

    /**
     * @var array
     */
    protected $searchResults;

    /**
     * @var array
     */
    protected $searchResultsDetails;

    /**
     * @inheritdoc
     */
    public function setUp()
    {
        $this->publicDirectory = vfsStream::setup('exampleDir');
        $this->eventService = $this->getMock(EventServiceInterface::class);
        $this->eventService->expects($this->any())
            ->method('getEvent')
            ->willReturnCallback(
                function ($eventId) {
                    return [
                        '@id' => 'http://example.com/event/' . $eventId,
                        '@type' => 'Event',
                        'foo' => 'bar',
                    ];
                }
            );

        $this->searchService = $this->getMock(SearchServiceInterface::class);
        $this->uuidGenerator = $this->getMock(UuidGeneratorInterface::class);
        $this->iriGenerator = $this->getMock(IriGeneratorInterface::class);
        $this->mailer = $this->getMock(NotificationMailerInterface::class);

        $this->eventExportService = new EventExportService(
            $this->eventService,
            $this->searchService,
            $this->uuidGenerator,
            $this->publicDirectory->url(),
            $this->iriGenerator,
            $this->mailer
        );

        $amount = 19;
        $range = range(1, $amount);
        $this->searchResults = array_map(
            function ($i) {
                return [
                    '@id' => 'http://example.com/event/' . $i,
                    '@type' => 'Event'
                ];
            },
            $range
        );

        $this->searchResultsDetails = array_map(
            function ($item) {
                return $item + ['foo' => 'bar'];
            },
            $this->searchResults
        );
        $this->searchResultsDetails = array_combine(
            $range,
            $this->searchResultsDetails
        );

        $this->searchService->expects($this->exactly(3))
            ->method('search')
            ->withConsecutive(
                [$this->anything(), 1, 0],
                [$this->anything(), 10, 0],
                [$this->anything(), 10, 10]
            )
            ->willReturnOnConsecutiveCalls(
                new Results(
                    array_slice($this->searchResults, 0, 1),
                    new Integer($amount)
                ),
                new Results(
                    array_slice($this->searchResults, 0, 10),
                    new Integer($amount)
                ),
                new Results(
                    array_slice($this->searchResults, 10),
                    new Integer($amount)
                )
            );
    }

    /**
     * @test
     */
    public function it_exports_events_to_a_file()
    {
        $exportUuid = 'abc';
        $this->uuidGenerator->expects($this->any())
            ->method('generate')
            ->willReturn($exportUuid);

        $exportExtension = 'txt';
        /** @var FileFormatInterface|\PHPUnit_Framework_MockObject_MockObject $fileFormat */
        $fileFormat = $this->getMock(FileFormatInterface::class);
        $fileFormat->expects($this->any())
            ->method('getFileNameExtension')
            ->willReturn($exportExtension);

        $expectedExportFileName = 'abc.txt';

        $fileWriter = $this->getMock(FileWriterInterface::class);
        $fileFormat->expects($this->any())
            ->method('getWriter')
            ->willReturn(
                $fileWriter
            );

        $fileWriter->expects($this->once())
            ->method('write')
            ->willReturnCallback(
                function ($tmpPath, $events) {
                    $contents = json_encode(iterator_to_array($events));
                    file_put_contents($tmpPath, $contents);
                }
            );

        $query = new EventExportQuery('city:Leuven');
        $logger = $this->getMock(LoggerInterface::class);

        $this->eventExportService->exportEvents(
            $fileFormat,
            $query,
            null,
            $logger
        );

        $this->assertTrue(
            $this->publicDirectory->hasChild($expectedExportFileName)
        );

        /** @var vfsStreamFile $file */
        $file = $this->publicDirectory->getChild($expectedExportFileName);

        $this->assertEquals(
            json_encode($this->searchResultsDetails),
            $file->getContent()
        );
    }

    /**
     * @test
     */
    public function it_sends_an_email_with_a_link_to_the_export_if_address_is_provided()
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     */
    public function it_ignores_items_that_can_not_be_found_by_the_event_service()
    {
        $this->markTestIncomplete();
    }
}
