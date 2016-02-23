<?php

namespace CultuurNet\UDB3\Search;

interface ResultIdExtractorInterface
{
    /**
     * @param mixed $result
     *   A search result.
     * @return string
     *   The id of the search result.
     */
    public function extract($result);
}
