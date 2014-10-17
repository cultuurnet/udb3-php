<?php
/**
 * @file
 */

namespace CultuurNet\UDB3;

use CultuurNet\Search\Parameter;
use CultuurNet\Search\SearchResult;
use CultuurNet\UDB3\Cdb\EventLD;
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

        $response = $this->searchAPI2->search($params);

        $result = SearchResult::fromXml(new \SimpleXMLElement($response->getBody(true), 0, false, \CultureFeed_Cdb_Default::CDB_SCHEME_URL));
        
        // @todo split this off to another class
        // @todo context and type should probably be injected at a higher level.
        $return = array(
            '@context' => 'http://www.w3.org/ns/hydra/context.jsonld',
            '@type' => 'PagedCollection',
            'itemsPerPage' => $limit,
            'totalItems' => $result->getTotalCount(),
            'member' => array(),
        );

        foreach ($result->getItems() as $item) {
            /** @var \CultureFeed_Cdb_Item_Event $event */
            $cdbEvent = $item->getEntity();
            $event = new EventLD($cdbEvent);
            $return['member'][] = $event;
        }

        return $return;
    }
}
