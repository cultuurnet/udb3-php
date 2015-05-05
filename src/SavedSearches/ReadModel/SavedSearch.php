<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\SavedSearches\ReadModel;

use CultuurNet\UDB3\SavedSearches\Properties\QueryString;
use ValueObjects\String\String;

class SavedSearch implements \JsonSerializable
{
    /**
     * @var String
     */
    protected $id;

    /**
     * @var String
     */
    protected $name;

    /**
     * @var QueryString
     */
    protected $query;

    /**
     * @param String $name
     * @param QueryString $query
     * @param String $id
     */
    public function __construct(String $name, QueryString $query, String $id = null)
    {
        $this->name = $name;
        $this->query = $query;
        $this->id = $id;
    }

    /**
     * @inheritdoc
     */
    public function jsonSerialize()
    {
        $serializedSavedSearch = [
            'name' => $this->name->toNative(),
            'query' => $this->query->toNative()
        ];

        if ($this->id) {
            $serializedSavedSearch['id'] = (string) $this->id;
        }

        return $serializedSavedSearch;
    }
}
