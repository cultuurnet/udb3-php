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
     * @param String $id
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
