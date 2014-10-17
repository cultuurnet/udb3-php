<?php
/**
 * @file
 */

namespace CultuurNet\UDB3;


class PullParsingSearchService extends LegacySearchService {

    public function search($query, $limit = 30, $start = 0)
    {
        $response = $this->_search($query, $limit, $start);

        $parser = new SearchAPI2\ResultSetPullParser(new \XMLReader());

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
} 
