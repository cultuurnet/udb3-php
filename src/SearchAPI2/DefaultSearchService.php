<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\SearchAPI2;

use CultuurNet\Auth\Guzzle\OAuthProtectedService;
use CultuurNet\Search\Guzzle\Parameter\Collector;

/**
 * Search API 2 default implementation.
 */
class DefaultSearchService extends OAuthProtectedService implements SearchServiceInterface
{
    /**
     * {@inheritdoc}
     */
    public function search(array $params)
    {
        $request = $this->getClient()->get('search');

        $collector = new Collector();
        $collector->addParameters($params, $request->getQuery());
        
        $response = $request->send();

        return $response;
    }
} 
