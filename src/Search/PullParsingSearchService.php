<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Search;

use CultuurNet\UDB3\SearchAPI2;

/**
 * SearchServiceInterface implementation that parses the results with a XML pull
 * parser, decreasing the memory usage and improving speed.
 */
class PullParsingSearchService extends LegacySearchService
{
    /**
     * @var SearchAPI2\ResultSetPullParser
     */
    protected $pullParser;

    /**
     * {@inheritdoc}
     */
    public function search($query, $limit = 30, $start = 0, $sort = null, $unavailable = true, $past = true)
    {
        $response = $this->_search($query, $limit, $start, $sort, $unavailable, $past);

        $parser = $this->getPullParser();

        $return = $parser->getResultSet($response->getBody(true));

        // @todo split this off to another class
        // @todo context and type should probably be injected at a higher level.
        $return = array(
                '@context' => 'http://www.w3.org/ns/hydra/context.jsonld',
                '@type' => 'PagedCollection',
                'itemsPerPage' => $limit,
            ) + $return;

        return $return;
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
