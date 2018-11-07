<?php

namespace CultuurNet\UDB3\SavedSearches\Command;

use CultuurNet\Deserializer\JSONDeserializer;
use CultuurNet\Deserializer\MissingValueException;
use CultuurNet\UDB3\SavedSearches\Properties\QueryString;
use CultuurNet\UDB3\ValueObject\SapiVersion;
use ValueObjects\StringLiteral\StringLiteral;

/**
 * @todo Move to udb3-symfony-php.
 * @see https://jira.uitdatabank.be/browse/III-1436
 */
class SubscribeToSavedSearchJSONDeserializer extends JSONDeserializer
{
    /**
     * @var StringLiteral $userId
     */
    protected $userId;

    /**
     * @param StringLiteral $userId
     */
    public function __construct(StringLiteral $userId)
    {
        parent::__construct();
        $this->userId = $userId;
    }

    /**
     * @param StringLiteral $data
     * @return SubscribeToSavedSearch|\stdClass
     */
    public function deserialize(StringLiteral $data)
    {
        $json = parent::deserialize($data);

        if (!isset($json->sapiVersion)) {
            throw new MissingValueException('sapiVersion is missing');
        }

        if (!isset($json->name)) {
            throw new MissingValueException('name is missing');
        }

        if (!isset($json->query)) {
            throw new MissingValueException('query is missing');
        }

        return new SubscribeToSavedSearch(
            new SapiVersion($json->sapiVersion),
            $this->userId,
            new StringLiteral($json->name),
            new QueryString($json->query)
        );
    }
}
