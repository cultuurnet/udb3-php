<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\EventExport;

use Broadway\UuidGenerator\UuidGeneratorInterface;
use CultuurNet\UDB3\EventExport\Notification\NotificationMailerInterface;
use CultuurNet\UDB3\EventNotFoundException;
use CultuurNet\UDB3\EventServiceInterface;
use CultuurNet\UDB3\Iri\IriGeneratorInterface;
use CultuurNet\UDB3\Search\Results;
use CultuurNet\UDB3\Search\SearchServiceInterface;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use org\bovigo\vfs\vfsStreamFile;
use PHPUnit_Framework_TestCase;
use Psr\Log\LoggerInterface;
use Traversable;
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
     * @param string $fileNameExtension
     *
     * @return FileFormatInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getFileFormat($fileNameExtension)
    {
        /** @var FileFormatInterface|\PHPUnit_Framework_MockObject_MockObject $fileFormat */
        $fileFormat = $this->getMock(FileFormatInterface::class);

        $fileFormat->expects($this->any())
            ->method('getFileNameExtension')
            ->willReturn($fileNameExtension);

        $fileWriter = $this->getMock(FileWriterInterface::class);
        $fileFormat->expects($this->any())
            ->method('getWriter')
            ->willReturn(
                $fileWriter
            );

        $fileWriter->expects($this->once())
            ->method('write')
            ->willReturnCallback(
                function ($tmpPath, Traversable $events) {
                    $contents = json_encode(iterator_to_array($events));
                    file_put_contents($tmpPath, $contents);
                }
            );

        return $fileFormat;
    }

    private function forceUuidGeneratorToReturn($uuid)
    {
        $this->uuidGenerator->expects($this->any())
            ->method('generate')
            ->willReturn($uuid);
    }

    /**
     * @test
     */
    public function it_exports_events_to_a_file()
    {
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

        $exportUuid = 'abc';
        $this->forceUuidGeneratorToReturn($exportUuid);

        $exportExtension = 'txt';

        $fileFormat = $this->getFileFormat($exportExtension);

        $expectedExportFileName = 'abc.txt';

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

        $this->assertJsonStringEqualsJsonString(
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
     * @param array $results
     * @param array $without
     * @return array
     */
    private function searchResultsWithout($results, $without)
    {
        $newResults = [];
        foreach ($results as $offerId => $result) {
            if (in_array($offerId, $without)) {
                continue;
            }

            $newResults[$offerId] = $result;
        }

        return $newResults;
    }

    /**
     * @test
     */
    public function it_ignores_items_that_can_not_be_found_by_the_event_service()
    {
        $unavailableEventIds = [3, 6, 17];
        $expectedDetails = $this->searchResultsWithout(
            $this->searchResultsDetails,
            $unavailableEventIds
        );

        foreach ($unavailableEventIds as $unavailableEventId) {
            $this->assertArrayNotHasKey($unavailableEventId, $expectedDetails);
        }

        $this->eventService->expects($this->any())
            ->method('getEvent')
            ->willReturnCallback(
                function ($eventId) use ($unavailableEventIds) {
                    if (in_array($eventId, $unavailableEventIds)) {
                        throw new EventNotFoundException(
                            "Event with cdbid {$eventId} could not be found via Entry API."
                        );
                    }

                    return [
                        '@id' => 'http://example.com/event/' . $eventId,
                        '@type' => 'Event',
                        'foo' => 'bar',
                    ];
                }
            );

        $query = new EventExportQuery('city:Leuven');

        $exportUuid = 'abc';
        $this->forceUuidGeneratorToReturn($exportUuid);

        $exportExtension = 'txt';
        $fileFormat = $this->getFileFormat($exportExtension);

        $expectedExportFileName = 'abc.txt';

        $this->eventExportService->exportEvents(
            $fileFormat,
            $query
        );

        /** @var vfsStreamFile $file */
        $file = $this->publicDirectory->getChild($expectedExportFileName);

        $this->assertJsonStringEqualsJsonString(
            json_encode($expectedDetails),
            $file->getContent()
        );
    }
}
