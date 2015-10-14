<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\SearchAPI2;

use CultuurNet\Auth\Guzzle\OAuthProtectedService;
use CultuurNet\Search\Guzzle\Parameter\Collector;
use CultuurNet\Search\Parameter;

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

        $params[] = new Parameter\FilterQuery('type:event');

        // include past events and present events with an embargo date
        $params[] = new Parameter\BooleanParameter('past', true);
        $params[] = new Parameter\BooleanParameter('unavailable', true);

        $collector = new Collector();
        $collector->addParameters($params, $request->getQuery());
        
        $response = $request->send();

        return $response;
    }
}
