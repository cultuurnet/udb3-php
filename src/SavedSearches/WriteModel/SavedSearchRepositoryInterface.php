<?php

namespace CultuurNet\UDB3\SavedSearches\WriteModel;

use CultuurNet\UDB3\SavedSearches\Properties\QueryString;
use ValueObjects\String\String;

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

    /**
     * @param String $userId
     * @param String $searchId
     */
    public function delete(
        String $userId,
        String $searchId
    );
}
