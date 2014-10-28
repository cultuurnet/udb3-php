<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\SearchAPI2;

use CultuurNet\Search\Parameter\Parameter;
use Guzzle\Http\Message\Response;

/**
 * Interface for the SearchAPI2 search service, in the context of UDB3.
 */
interface SearchServiceInterface
{
    /**
     * Finds CDB items.
     *
     * @param Parameter[] $params
     *
     * @return Response
     *   The response. Sending back the whole response, allows for a lot of
     *   flexibility on the side of the caller.
     */
    public function search(array $params);
}
