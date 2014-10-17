<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\SearchAPI2;

use CultuurNet\Auth\Guzzle\OAuthProtectedService;
use CultuurNet\Search\Parameter\Parameter;
use Guzzle\Http\Message\Response;

interface SearchServiceInterface
{
    /**
     * @param Parameter[] $params
     *
     * @return Response
     *   The response. Sending back the whole response, allows for a lot of
     *   flexibility on the side of the caller.
     */
    public function search(array $params);
}
