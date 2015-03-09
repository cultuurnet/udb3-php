<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\UDB2;

use CultuurNet\Search\Parameter\Query;
use CultuurNet\UDB3\SearchAPI2\SearchServiceInterface;

class EventCdbXmlFromSearchService implements EventCdbXmlServiceInterface
{
    /**
     * @var SearchServiceInterface
     */
    private $search;

    /**
     * @param SearchServiceInterface $search
     */
    public function __construct(SearchServiceInterface $search)
    {
        $this->search = $search;
    }

    public function getCdbXmlOfEvent($eventId)
    {
        $results = $this->search->search(
            [
                new Query('cdbid:"' . $eventId . '"')
            ]
        );

        $cdbXml = $results->getBody(true);

        $reader = new \XMLReader();

        $reader->xml($cdbXml);

        while ($reader->read()) {
            switch ($reader->nodeType) {
                case ($reader::ELEMENT):
                    if ($reader->localName == "event" &&
                        $reader->getAttribute('cdbid') == $eventId
                    ) {
                        $node = $reader->expand();
                        $dom = new \DomDocument('1.0');
                        $n = $dom->importNode($node, true);
                        $dom->appendChild($n);
                        $eventXml = $dom->saveXML();
                    }
            }
        }

        if (!isset($eventXml)) {
            throw new EventNotFoundException(
                "Event with cdbid '{$eventId}' could not be found via Search API v2."
            );
        } else {
            return $eventXml;
        }
    }
}
