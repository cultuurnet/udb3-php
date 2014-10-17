<?php
/**
 * @file
 */

namespace CultuurNet\UDB3;

use CultuurNet\Search\Parameter;
use CultuurNet\Search\SearchResult;
use CultuurNet\UDB3\SearchAPI2;

/**
 * Search service implementation using Search API v2.
 */
class DefaultSearchService implements SearchServiceInterface
{
    /**
     * @var SearchAPI2\SearchServiceInterface
     */
    protected $searchAPI2;

    public function __construct(SearchAPI2\SearchServiceInterface $search)
    {
        $this->searchAPI2 = $search;
    }

    public function search($query, $limit = 30, $start = 0)
    {
        $q = new Parameter\Query($query);
        $start = new Parameter\Start($start);
        $limit = new Parameter\Rows($limit);

        $params = array(
            $q,
            $limit,
            $start
        );

        $response = $this->searchAPI2->search($params);

        $result = SearchResult::fromXml(new \SimpleXMLElement($response->getBody(true), 0, false, \CultureFeed_Cdb_Default::CDB_SCHEME_URL));
        
        // @todo split this off to another class
        $return = array(
            'total' => $result->getTotalCount(),
            'results' => array(),
        );

        foreach ($result->getItems() as $item) {
            $return['results'][] = array(
                '@id' => $item->getId(),
                // @todo Language should be configurable.
                'title' => $item->getTitle('nl'),
            );
        }

        return $return;
    }
}
