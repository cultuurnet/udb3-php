<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\UDB2;

use CultuurNet\Search\Parameter\Parameter;
use CultuurNet\UDB3\SearchAPI2\SearchServiceInterface;
use Guzzle\Http\Message\Response;

class FixedResponseSearchService implements SearchServiceInterface
{
    /**
     * @var string
     */
    protected $searchEmptyResponse;

    /**
     * @var string
     */
    protected $fixedResponse;

    public function __construct()
    {
        $this->searchEmptyResponse = <<<EOS
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<cdb:cdbxml xmlns:cdb="http://www.cultuurdatabank.com/XMLSchema/CdbXSD/3.2/FINAL">
<cdb:nofrecords
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xmlns:xs="http://www.w3.org/2001/XMLSchema"
    xsi:type="xs:long">0</cdb:nofrecords>
</cdb:cdbxml>
EOS;
        $this->fixedResponse = $this->searchEmptyResponse;
    }

    /**
     * @param string $path
     */
    public function setFixedResponseFromFile($path)
    {
        $this->fixedResponse = file_get_contents($path);
    }

    /**
     * @inheritdoc
     */
    public function search(array $params)
    {
        return new Response(200, [], $this->fixedResponse);
    }
}
