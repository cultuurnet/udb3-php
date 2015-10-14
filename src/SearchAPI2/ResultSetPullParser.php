<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\SearchAPI2;

use CultuurNet\UDB3\Iri\IriGeneratorInterface;
use CultuurNet\UDB3\Search\Results;
use ValueObjects\Number\Integer;

/**
 * Parser using XML pull parsing to extract the ids from the CDBXML-formatted
 * results returned by Search API 2.
 */
class ResultSetPullParser
{
    /**
     * @var \XMLReader
     */
    protected $xmlReader;

    /**
     * @var \CultuurNet\UDB3\Iri\IriGeneratorInterface
     */
    protected $iriGenerator;

    /**
     * @param \XMLReader $xmlReader
     * @param \CultuurNet\UDB3\Iri\IriGeneratorInterface $iriGenerator
     */
    public function __construct(
        \XMLReader $xmlReader,
        IriGeneratorInterface $iriGenerator
    ) {
        $this->xmlReader = $xmlReader;
        $this->iriGenerator = $iriGenerator;
    }

    /**
     * Creates a result set.
     *
     * @param string $cdbxml
     *   The CDBXML-formatted search results.
     *
     * @return Results
     */
    public function getResultSet($cdbxml)
    {
        $items = [];
        $totalItems = null;

        $r = $this->xmlReader;

        $r->xml($cdbxml);

        while ($r->read()) {
            if ($r->nodeType == $r::ELEMENT && $r->localName == 'nofrecords') {
                $totalItems = new Integer((int)$r->readString());
            }

            if ($r->nodeType == $r::ELEMENT && ($r->localName == 'event' || $r->localName == 'actor')) {
                $items[] = array(
                    '@id' => $this->iriGenerator->iri(
                        $r->getAttribute('cdbid')
                    ),
                );
            }
        }

        return new Results($items, $totalItems);
    }
}
