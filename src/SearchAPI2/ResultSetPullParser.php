<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\SearchAPI2;


use CultuurNet\UDB3\IriGeneratorInterface;

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
     */
    public function __construct(\XMLReader $xmlReader, IriGeneratorInterface $iriGenerator)
    {
        $this->xmlReader = $xmlReader;
        $this->iriGenerator = $iriGenerator;
    }

    /**
     * @param string $cdbxml
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
