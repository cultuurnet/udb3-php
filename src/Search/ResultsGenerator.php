<?php

namespace CultuurNet\UDB3\Search;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;

class ResultsGenerator implements LoggerAwareInterface, ResultsGeneratorInterface
{
    use LoggerAwareTrait;

    /**
     * Default sorting method because it's ideal for getting consistent paging
     * results.
     */
    const SORT_CREATION_DATE_ASC = 'creationdate asc';

    /**
     * @var SearchServiceInterface
     */
    private $searchService;

    /**
     * @var string
     */
    private $sorting;

    /**
     * @var int
     */
    private $pageSize;

    /**
     * @param SearchServiceInterface $searchService
     * @param string $sorting
     * @param int $pageSize
     */
    public function __construct(
        SearchServiceInterface $searchService,
        $sorting = self::SORT_CREATION_DATE_ASC,
        $pageSize = 10
    ) {
        $this->searchService = $searchService;
        $this->sorting = $sorting;
        $this->pageSize = $pageSize;

        // Set a default logger so we don't need to check if a logger is set
        // when we actually try to log something. This can easily be overridden
        // from outside as this method is public.
        $this->setLogger(new NullLogger());
    }

    /**
     * @param string $sorting
     * @return ResultsGenerator
     */
    public function withSorting($sorting)
    {
        $c = clone $this;
        $c->sorting = $sorting;
        return $c;
    }

    /**
     * @return string
     */
    public function getSorting()
    {
        return $this->sorting;
    }

    /**
     * @param string $pageSize
     * @return ResultsGenerator
     */
    public function withPageSize($pageSize)
    {
        $c = clone $this;
        $c->pageSize = $pageSize;
        return $c;
    }

    /**
     * @return int
     */
    public function getPageSize()
    {
        return $this->pageSize;
    }

    /**
     * @param string $query
     * @return \Iterator
     */
    public function search(string $query)
    {
        $currentPage = 0;
        $ids = [];

        do {
            $results = $this->searchService->search(
                $query,
                $this->pageSize,
                $this->pageSize * $currentPage,
                $this->sorting
            );

            $total = $results->getTotalItems()->toNative();

            foreach ($results->getItems() as $item) {
                $id = $item->getId();

                if (!isset($ids[$id])) {
                    // Store result id with current page in case we run into
                    // the same id again later.
                    $ids[$id] = $currentPage;
                    yield $id => $item;
                } else {
                    $this->logger->error(
                        'query_duplicate_event',
                        array(
                            'query' => $query,
                            'error' => "Found duplicate offer {$id} on page {$currentPage}, " .
                                "occurred first time on page {$ids[$id]}.",
                        )
                    );
                }
            }

            $currentPage++;
        } while ($currentPage < ceil($total / $this->pageSize));
    }
}
