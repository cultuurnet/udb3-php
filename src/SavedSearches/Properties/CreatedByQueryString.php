<?php

namespace CultuurNet\UDB3\SavedSearches\Properties;

class CreatedByQueryString extends QueryString
{
    /**
     * @param array $queryParts
     */
    public function __construct(array $queryParts)
    {
        if (empty($queryParts)) {
            throw new \InvalidArgumentException('At least one query part is required.');
        }

        $query = implode(' OR ', $queryParts);
        if (count($queryParts) > 1) {
            $query = '(' . $query . ')';
        }
        $query = 'createdby:' . $query;

        parent::__construct($query);
    }
}
