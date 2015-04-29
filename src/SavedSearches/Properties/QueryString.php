<?php

namespace CultuurNet\UDB3\SavedSearches\Properties;

use ValueObjects\String\String;

class QueryString extends String
{
    /**
     * @param $queryString
     * @return QueryString
     */
    public static function fromURLQueryString($queryString)
    {
        parse_str($queryString, $queryArray);

        if (!isset($queryArray['q'])) {
            throw new \InvalidArgumentException('Provided query string should contain a parameter named "q".');
        }

        return new QueryString($queryArray['q']);
    }
}
