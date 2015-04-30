<?php

namespace CultuurNet\UDB3\SavedSearches\WriteModel;

use ValueObjects\String\String;
use CultuurNet\UDB3\SavedSearches\Properties\QueryString;

interface SavedSearchRepositoryInterface
{
    /**
     * @param String $userId
     * @param String $name
     * @param QueryString $queryString
     * @return
     */
    public function write(
        String $userId,
        String $name,
        QueryString $queryString
    );
}
