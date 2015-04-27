<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\SavedSearches\ReadModel;

class SavedSearch implements \JsonSerializable
{
    protected $id;
    protected $name;
    protected $query;

    /**
     * @param string $name
     * @param string $query
     * @param string $id
     */
    public function __construct($name, $query, $id = null)
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
        return [
            'id' => $this->id,
            'name' => $this->name,
            'query' => $this->query
        ];
    }
}
