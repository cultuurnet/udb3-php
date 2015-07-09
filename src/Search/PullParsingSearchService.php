<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Search;

use CultuurNet\Search\Parameter;
use CultuurNet\UDB3\Iri\IriGeneratorInterface;
use CultuurNet\UDB3\SearchAPI2;

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
    protected $iriGenerator;

    /**
     * Constructs a new PullParsingSearchService
     *
     * @param SearchAPI2\SearchServiceInterface $search
     * @param IriGeneratorInterface $iriGenerator
     */
    public function __construct(SearchAPI2\SearchServiceInterface $search, IriGeneratorInterface $iriGenerator)
    {
        $this->searchAPI2 = $search;
        $this->iriGenerator = $iriGenerator;
    }

    /**
     * {@inheritdoc}
     */
    public function search($query, $limit = 30, $start = 0, $sort = null)
    {
        $response = $this->executeSearch($query, $limit, $start, $sort);

        $parser = $this->getPullParser();

        return $parser->getResultSet($response->getBody(true));
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

        $params = array(
            $qParam,
            $groupParam,
            $limitParam,
            $startParam
        );

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
                $this->iriGenerator
            );
        }
        return $this->pullParser;
    }
}
