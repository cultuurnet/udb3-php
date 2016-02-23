<?php

namespace CultuurNet\UDB3\Search;

use CultuurNet\UDB3\Offer\OfferType;

interface ResultTypeExtractorInterface
{
    /**
     * @param mixed $result
     *   A search result.
     * @return OfferType
     *   The type of the search result.
     */
    public function extract($result);
}
