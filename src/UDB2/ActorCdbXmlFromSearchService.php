<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\UDB2;

use CultuurNet\Search\Parameter\FilterQuery;
use CultuurNet\Search\Parameter\Query;
use CultuurNet\UDB3\SearchAPI2\SearchServiceInterface;

class ActorCdbXmlFromSearchService implements ActorCdbXmlServiceInterface
{
    /**
     * @var SearchServiceInterface
     */
    protected $search;

    /**
     * @var string
     */
    private $cdbXmlNamespaceUri;

    public function __construct(
        SearchServiceInterface $search,
        $cdbXmlNamespaceUri = 'http://www.cultuurdatabank.com/XMLSchema/CdbXSD/3.2/FINAL'
    ) {
        $this->cdbXmlNamespaceUri = $cdbXmlNamespaceUri;
        $this->search = $search;
    }

    /**
     * @return string
     */
    public function getCdbXmlNamespaceUri()
    {
        return $this->cdbXmlNamespaceUri;
    }

    /**
     * @param string $actorId
     * @return string
     * @throws ActorNotFoundException If the actor can not be found.
     */
    public function getCdbXmlOfActor($actorId)
    {
        $results = $this->search->search(
            [
                new Query('cdbid:"' . $actorId . '"'),
                new FilterQuery('type:actor')
            ]
        );

        $cdbXml = $results->getBody(true);

        $reader = new \XMLReader();

        $reader->xml($cdbXml);

        while ($reader->read()) {
            switch ($reader->nodeType) {
                case ($reader::ELEMENT):
                    if ($reader->localName == "actor" &&
                        $reader->getAttribute('cdbid') == $actorId
                    ) {
                        if ($reader->namespaceURI !== $this->cdbXmlNamespaceUri) {
                            // @todo Use a more specific exception here.
                            throw new \RuntimeException('Namespace URI does not match, expected ' . $this->cdbXmlNamespaceUri . ', actual ' . $reader->namespaceURI);
                        }

                        $node = $reader->expand();
                        $dom = new \DomDocument('1.0');
                        $n = $dom->importNode($node, true);
                        $dom->appendChild($n);
                        $actorXml = $dom->saveXML();
                    }
            }
        }

        if (!isset($actorXml)) {
            throw new ActorNotFoundException(
                "Actor with cdbid '{$actorId}' could not be found via Search API v2."
            );
        }

        return $actorXml;
    }
}
