<?php

namespace CultuurNet\UDB3\Search;

use CultuurNet\Search\Parameter;
use CultuurNet\UDB3\Iri\IriGeneratorInterface;
use CultuurNet\UDB3\SearchAPI2;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * SearchServiceInterface implementation that uses Search API 2 and parses
 * results with a XML pull parser, decreasing the memory usage and improving
 * speed.
 */
class PullParsingSearchService implements SearchServiceInterface
{
    /**
     * @var SearchAPI2\ResultSetPullParser
     */
    protected $pullParser;

    /**
     * @var SearchAPI2\SearchServiceInterface
     */
    protected $searchAPI2;

    /**
     * @var IriGeneratorInterface
     */
    protected $eventIriGenerator;

    /**
     * @var IriGeneratorInterface
     */
    protected $placeIriGenerator;

    /**
     * @var LoggerInterface
     */
    protected $resultParserLogger;

    /**
     * @var bool
     */
    protected $includePrivateItems;

    /**
     * @param SearchAPI2\SearchServiceInterface $search
     * @param IriGeneratorInterface $eventIriGenerator
     * @param IriGeneratorInterface $placeIriGenerator
     * @param LoggerInterface|null $resultParserLogger
     */
    public function __construct(
        SearchAPI2\SearchServiceInterface $search,
        IriGeneratorInterface $eventIriGenerator,
        IriGeneratorInterface $placeIriGenerator,
        LoggerInterface $resultParserLogger = null
    ) {
        $this->searchAPI2 = $search;
        $this->eventIriGenerator = $eventIriGenerator;
        $this->placeIriGenerator = $placeIriGenerator;
        $this->resultParserLogger = $resultParserLogger ? $resultParserLogger : new NullLogger();

        $this->includePrivateItems = true;
    }

    /**
     * {@inheritdoc}
     */
    public function search(string $query, $limit = 30, $start = 0, $sort = null)
    {
        $response = $this->executeSearch($query, $limit, $start, $sort);

        $parser = $this->getPullParser();

        return $parser->getResultSet($response->getBody(true));
    }

    /**
     * @return PullParsingSearchService
     */
    public function doNotIncludePrivateItems()
    {
        $copy = clone $this;
        $copy->includePrivateItems = false;
        return $copy;
    }

    /**
     * Finds items matching an arbitrary query.
     *
     *  @param string $query
     *   An arbitrary query.
     * @param int $limit
     *   How many items to retrieve.
     * @param int $start
     *   Offset to start from.
     * @param string $sort
     *   Sorting to use.
     *
     * @return \Guzzle\Http\Message\Response
     */
    private function executeSearch($query, $limit, $start, $sort = null)
    {
        $qParam = new Parameter\Query($query);
        $groupParam = new Parameter\Group();
        $startParam = new Parameter\Start($start);
        $limitParam = new Parameter\Rows($limit);
        $typeParam = new Parameter\FilterQuery('type:event OR (type:actor AND category_id:8.15.0.0.0)');

        $params = array(
            $qParam,
            $groupParam,
            $limitParam,
            $startParam,
            $typeParam,
        );

        if ($this->includePrivateItems) {
            $privateParam = new Parameter\FilterQuery('private:*');

            $params[] = $privateParam;
        }

        if ($sort) {
            $params[] = new Parameter\Parameter('sort', $sort);
        }

        $response = $this->searchAPI2->search($params);

        return $response;
    }

    /**
     * @return SearchAPI2\ResultSetPullParser
     */
    protected function getPullParser()
    {
        if (!$this->pullParser) {
            $this->pullParser = new SearchAPI2\ResultSetPullParser(
                new \XMLReader(),
                $this->eventIriGenerator,
                $this->placeIriGenerator
            );
            $this->pullParser->setLogger($this->resultParserLogger);
        }
        return $this->pullParser;
    }
}
