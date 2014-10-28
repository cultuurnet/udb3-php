<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\SearchAPI2;


use CultuurNet\UDB3\IriGeneratorInterface;

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
     * @var IriGeneratorInterface
     */
    protected $iriGenerator;

    /**
     * @param \XMLReader $xmlReader
     * @param \CultuurNet\UDB3\IriGeneratorInterface $iriGenerator
     */
    public function __construct(\XMLReader $xmlReader, IriGeneratorInterface $iriGenerator)
    {
        $this->xmlReader = $xmlReader;
        $this->iriGenerator = $iriGenerator;
    }

    /**
     * Creates a result set.
     *
     * @param string $cdbxml
     *   The CDBXML-formatted search results.
     *
     * @return array
     *   Search results as a JSON-LD array.
     */
    public function getResultSet($cdbxml)
    {
        $results = array();

        $r = $this->xmlReader;

        $r->xml($cdbxml);

        while ($r->read()) {
            if ($r->nodeType == $r::ELEMENT && $r->localName == 'nofrecords') {
                $results['totalItems'] = (int)$r->readString();
            }

            if ($r->nodeType == $r::ELEMENT && $r->localName == 'event') {
                $results['member'][] = array(
                    '@id' => $this->iriGenerator->iri($r->getAttribute('cdbid')),
                );
            }
        }

        return $results;
    }
} 
