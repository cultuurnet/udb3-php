<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\SavedSearches\Command;

use CultuurNet\Deserializer\JSONDeserializer;
use CultuurNet\Deserializer\MissingValueException;
use ValueObjects\String\String;

class SubscribeToSavedSearchJSONDeserializer extends JSONDeserializer
{
    /**
     * @var string $userId
     */
    protected $userId;

    public function __construct($userId)
    {
        $this->userId = $userId;
    }

    public function deserialize(String $data)
    {
        $json = parent::deserialize($data);

        if (!isset($json->name)) {
            throw new MissingValueException('name is missing');
        }

        if (!isset($json->query)) {
            throw new MissingValueException('query is missing');
        }

        return new SubscribeToSavedSearch($this->userId, $json->name, $json->query);
    }
}
