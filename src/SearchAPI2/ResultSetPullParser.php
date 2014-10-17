<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\SearchAPI2;


class ResultSetPullParser
{
    /**
     * @var \XMLReader
     */
    protected $xmlReader;

    /**
     * @param \XMLReader $xmlReader
     */
    public function __construct(\XMLReader $xmlReader)
    {
        $this->xmlReader = $xmlReader;
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
                    '@id' => $r->getAttribute('cdbid'),
                );
            }
        }

        return $results;
    }
} 
