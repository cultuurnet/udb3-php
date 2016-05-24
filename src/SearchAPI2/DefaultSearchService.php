<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\SearchAPI2;

use CultuurNet\Auth\Guzzle\OAuthProtectedService;
use CultuurNet\Search\Guzzle\Parameter\Collector;
use CultuurNet\Search\Parameter\BooleanParameter;
use CultuurNet\Search\Parameter\Parameter;

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

        // use CdbXML 3.3
        $params[] = new Parameter('version', '3.3');

        // include past events and present events with an embargo date
        $params[] = new BooleanParameter('past', true);
        $params[] = new BooleanParameter('unavailable', true);
        $params[] = new BooleanParameter('udb3filtering', false);

        $collector = new Collector();
        $collector->addParameters($params, $request->getQuery());

        $response = $request->send();

        return $response;
    }
}
