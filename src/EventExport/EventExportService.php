<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\EventExport;


use Broadway\CommandHandling\CommandBusInterface;
use CultuurNet\UDB3\EventServiceInterface;
use CultuurNet\UDB3\Search\SearchServiceInterface;
use Guzzle\Http\Exception\ClientErrorResponseException;
use Psr\Log\LoggerInterface;
use Broadway\UuidGenerator\UuidGeneratorInterface;
use CultuurNet\UDB3\Iri\IriGeneratorInterface;

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
     * @var \Swift_Mailer
     */
    protected $mailer;

    /**
     * @param EventServiceInterface $eventService
     * @param SearchServiceInterface $searchService
     * @param UuidGeneratorInterface $uuidGenerator
     * @param string $publicDirectory
     * @param IriGeneratorInterface $iriGenerator
     * @param \Swift_Mailer $mailer
     */
    public function __construct(
        EventServiceInterface $eventService,
        SearchServiceInterface $searchService,
        UuidGeneratorInterface $uuidGenerator,
        $publicDirectory,
        IriGeneratorInterface $iriGenerator,
        \Swift_Mailer $mailer
    ) {
        $this->eventService = $eventService;
        $this->searchService = $searchService;
        $this->uuidGenerator = $uuidGenerator;
        $this->publicDirectory = $publicDirectory;
        $this->iriGenerator = $iriGenerator;
        $this->mailer = $mailer;
    }

    public function exportEventsAsJsonLD(
        EventExportQuery $query,
        $address = null,
        LoggerInterface $logger = null
    ) {
        // do a pre query to test if the query is valid and check the item count
        try {
            $preQueryResult = $this->searchService->search(
                (string)$query,
                1,
                0
            );
            $totalItemCount = $preQueryResult['totalItems'];
        } catch (ClientErrorResponseException $e) {
            if ($logger) {
                $logger->error(
                    'not_exported',
                    array(
                        'query' => (string)$query,
                        'error' => $e->getMessage(),
                        'exception_class' => get_class($e),
                    )
                );
            }

            throw ($e);
        }

        print($totalItemCount) . PHP_EOL;

        if ($totalItemCount < 1) {
            if ($logger) {
                $logger->error(
                    'not_exported',
                    array(
                        'query' => (string)$query,
                        'error' => "query did not return any results"
                    )
                );
            }

            return false;
        }

        try {
            $tmpPath = tempnam(
                sys_get_temp_dir(),
                $this->uuidGenerator->generate()
            );

            $tmpFile = fopen($tmpPath, 'w');
            fwrite($tmpFile, '[');
            $previous = false;
            foreach ($this->search(
                $totalItemCount,
                $query,
                $logger
            ) as $event) {
                if ($previous) {
                    fwrite($tmpFile, ',');
                }
                fwrite($tmpFile, $event);
                $previous = true;
            }
            fwrite($tmpFile, ']');

            fclose($tmpFile);

            $finalPath = realpath($this->publicDirectory) . '/' . basename(
                    $tmpPath
                ) . '.json';

            rename($tmpPath, $finalPath);

            $finalUrl = $this->iriGenerator->iri(
                basename($finalPath)
            );

            if ($logger) {
                $logger->info(
                    'exported',
                    [
                        'location' => $finalUrl,
                    ]
                );
            }

            if ($address) {
                $this->notifyByMail($address, $finalUrl);
            }
        } catch (\Exception $e) {
            if (is_resource($tmpFile)) {
                fclose($tmpFile);
            }

            if ($tmpPath) {
                unlink($tmpPath);
            }

            throw $e;
        }
    }

    /**
     * Generator that yields each unique search result.
     *
     * @param string $query
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
            foreach ($results['member'] as $event) {
                $expoId = explode('/', $event['@id']);
                $eventId = array_pop($expoId);

                if (!array_key_exists($eventId, $exportedEventIds)) {
                    $exportedEventIds[$eventId] = $pageCounter;

                    $event = $this->eventService->getEvent($eventId);

                    yield $event;
                } else {
                    if ($logger) {
                        $logger->error(
                            'query_duplicate_event',
                            array(
                                'query' => $query,
                                'error' => "found duplicate event {$eventId} on page {$pageCounter}, occurred first time on page {$exportedEventIds[$eventId]}"
                            )
                        );
                    }
                }
            }
            ++$pageCounter;
        };
    }

    /**
     * @param string $address
     * @param string $url
     */
    protected function notifyByMail($address, $url)
    {
        $message = new \Swift_Message('Uw export van evenementen');
        $message->setBody('<a href="' . $url . '">' . $url . '</a>', 'text/html');
        $message->addPart($url, 'text/plain');

        $message->addTo($address);

        // @todo Move this to config.yml.
        $message->setSender('no-reply@uitdatabank.be', 'UiTdatabank BETA');

        $sent = $this->mailer->send($message);

        print 'sent ' . $sent . ' e-mails' . PHP_EOL;
    }
}
