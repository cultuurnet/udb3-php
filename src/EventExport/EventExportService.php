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
use CultuurNet\UDB3\Search\SearchServiceInterface;
use Guzzle\Http\Exception\ClientErrorResponseException;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use ValueObjects\Web\EmailAddress;

class EventExportService implements EventExportServiceInterface
{
    /**
     * @var EventServiceInterface
     */
    protected $eventService;

    /**
     * @var SearchServiceInterface
     */
    protected $searchService;
    /**
     * @var UuidGeneratorInterface
     */
    protected $uuidGenerator;

    /**
     * Publicly accessible directory where exports will be stored.
     *
     * @var string
     */
    protected $publicDirectory;

    /**
     * @var NotificationMailerInterface
     */
    protected $mailer;

    /**
     * @var IriGeneratorInterface
     */
    protected $iriGenerator;

    /**
     * @param EventServiceInterface $eventService
     * @param SearchServiceInterface $searchService
     * @param UuidGeneratorInterface $uuidGenerator
     * @param string $publicDirectory
     * @param IriGeneratorInterface $iriGenerator
     * @param NotificationMailerInterface $mailer
     */
    public function __construct(
        EventServiceInterface $eventService,
        SearchServiceInterface $searchService,
        UuidGeneratorInterface $uuidGenerator,
        $publicDirectory,
        IriGeneratorInterface $iriGenerator,
        NotificationMailerInterface $mailer
    ) {
        $this->eventService = $eventService;
        $this->searchService = $searchService;
        $this->uuidGenerator = $uuidGenerator;
        $this->publicDirectory = $publicDirectory;
        $this->iriGenerator = $iriGenerator;
        $this->mailer = $mailer;
    }

    /**
     * @param FileFormatInterface $fileFormat
     *  The file format of the exported file.
     *
     * @param EventExportQuery $query
     *  The query that will be exported.
     *  A query has to be specified even if you are exporting a selection of events.
     *
     * @param EmailAddress|null $address
     *  An optional email address that will receive an email containing the exported file.
     *
     * @param LoggerInterface|null $logger
     *  An optional logger that reports unknown events and empty exports.
     *
     * @param string[]|null $selection
     *  A selection of items that will be included in the export.
     *  When left empty the whole query will export.
     *
     * @return bool|string
     *  The destination url of the export file or false if no events were found.
     */
    public function exportEvents(
        FileFormatInterface $fileFormat,
        EventExportQuery $query,
        EmailAddress $address = null,
        LoggerInterface $logger = null,
        $selection = null
    ) {
        if (!$logger instanceof LoggerInterface) {
            $logger = new NullLogger();
        }

        // do a pre query to test if the query is valid and check the item count
        try {
            $preQueryResult = $this->searchService->search(
                (string)$query,
                1,
                0
            );
            $totalItemCount = $preQueryResult->getTotalItems()->toNative();
        } catch (ClientErrorResponseException $e) {
            $logger->error(
                'not_exported',
                array(
                    'query' => (string)$query,
                    'error' => $e->getMessage(),
                    'exception_class' => get_class($e),
                )
            );

            throw ($e);
        }

        print($totalItemCount) . PHP_EOL;

        if ($totalItemCount < 1) {
            $logger->error(
                'not_exported',
                array(
                    'query' => (string)$query,
                    'error' => "query did not return any results"
                )
            );

            return false;
        }

        try {
            $tmpDir = sys_get_temp_dir();
            $tmpFileName = $this->uuidGenerator->generate();
            $tmpPath = "{$tmpDir}/{$tmpFileName}";

            // $events are keyed here by the authoritative event ID.
            if (is_array($selection)) {
                $events = $this->getEventsAsJSONLD($selection, $logger);
            } else {
                $events = $this->search(
                    $totalItemCount,
                    $query,
                    $logger
                );
            }

            $fileWriter = $fileFormat->getWriter();
            $fileWriter->write($tmpPath, $events);

            $finalPath = $this->getFinalFilePath($fileFormat, $tmpPath);

            $moved = copy($tmpPath, $finalPath);
            unlink($tmpPath);

            if (!$moved) {
                throw new \RuntimeException(
                    'Unable to move export file to public directory ' .
                    $this->publicDirectory
                );
            }

            $finalUrl = $this->iriGenerator->iri(
                basename($finalPath)
            );

            $logger->info(
                'job_info',
                [
                    'location' => $finalUrl,
                ]
            );

            if ($address) {
                $this->notifyByMail($address, $finalUrl);
            }

            return $finalUrl;
        } catch (\Exception $e) {
            if (isset($tmpPath) && $tmpPath && file_exists($tmpPath)) {
                unlink($tmpPath);
            }

            throw $e;
        }
    }

    /**
     * Get all events formatted as JSON-LD.
     *
     * @param \Traversable $events
     * @param LoggerInterface $logger
     * @return \Generator
     */
    private function getEventsAsJSONLD($events, LoggerInterface $logger)
    {
        foreach ($events as $eventId) {
            $event = $this->getEvent($eventId, $logger);

            if ($event) {
                yield $eventId => $event;
            }
        }
    }

    /**
     * @param string $id
     *   A string uniquely identifying an event.
     *
     * @param LoggerInterface $logger
     *
     * @return array|null
     *   An event array or null if the event was not found.
     */
    private function getEvent($id, LoggerInterface $logger)
    {
        try {
            $event = $this->eventService->getEvent($id);
        } catch (EventNotFoundException $e) {
            $logger->error(
                $e->getMessage(),
                [
                    'eventId' => $id,
                    'exception' => $e,
                ]
            );

            $event = null;
        }

        return $event;
    }

    /**
     * @param FileFormatInterface $fileFormat
     * @param string $tmpPath
     * @return string
     */
    private function getFinalFilePath(
        FileFormatInterface $fileFormat,
        $tmpPath
    ) {
        $fileUniqueId = basename($tmpPath);
        $extension = $fileFormat->getFileNameExtension();
        $finalFileName = $fileUniqueId . '.' . $extension;
        $finalPath = $this->publicDirectory . '/' . $finalFileName;

        return $finalPath;
    }

    /**
     * Generator that yields each unique search result.
     *
     * @param int $totalItemCount
     * @param string|object $query
     * @param LoggerInterface $logger
     *
     * @return \Generator
     */
    private function search($totalItemCount, $query, LoggerInterface $logger)
    {
        // change this pageSize value to increase or decrease the page size;
        $pageSize = 10;
        $pageCount = ceil($totalItemCount / $pageSize);
        $pageCounter = 0;
        $exportedEventIds = [];

        // Page querying the search service;
        while ($pageCounter < $pageCount) {
            $start = $pageCounter * $pageSize;
            // Sort ascending by creation date to make sure we get a quite consistent paging.
            $sort = 'creationdate asc';
            $results = $this->searchService->search(
                (string)$query,
                $pageSize,
                $start,
                $sort
            );

            // Iterate the results of the current page and get their IDs
            // by stripping them from the json-LD representation
            foreach ($results->getItems() as $event) {
                $expoId = explode('/', $event['@id']);
                $eventId = array_pop($expoId);

                if (!array_key_exists($eventId, $exportedEventIds)) {
                    $exportedEventIds[$eventId] = $pageCounter;

                    $event = $this->getEvent($eventId, $logger);

                    if ($event) {
                        yield $eventId => $event;
                    }
                } else {
                    $logger->error(
                        'query_duplicate_event',
                        array(
                            'query' => $query,
                            'error' => "found duplicate event {$eventId} on page {$pageCounter}, occurred first time on page {$exportedEventIds[$eventId]}"
                        )
                    );
                }
            }
            ++$pageCounter;
        };
    }

    /**
     * @param EmailAddress $address
     * @param string $url
     */
    protected function notifyByMail(EmailAddress $address, $url)
    {
        $this->mailer->sendNotificationMail(
            $address,
            new EventExportResult($url)
        );
    }
}
