<?php

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
     * @var IriGeneratorInterface
     */
    protected $eventIriGenerator;

    /**
     * @var IriGeneratorInterface
     */
    protected $placeIriGenerator;

    /**
     * @param \XMLReader $xmlReader
     * @param IriGeneratorInterface $eventIriGenerator
     * @param IriGeneratorInterface $placeIriGenerator
     */
    public function __construct(
        \XMLReader $xmlReader,
        IriGeneratorInterface $eventIriGenerator,
        IriGeneratorInterface $placeIriGenerator
    ) {
        $this->xmlReader = $xmlReader;
        $this->eventIriGenerator = $eventIriGenerator;
        $this->placeIriGenerator = $placeIriGenerator;
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

        $currentEventCdbId = null;
        $currentEventIsUdb3Place = false;

        while ($r->read()) {
            if ($r->nodeType == $r::ELEMENT && $r->localName == 'nofrecords') {
                $totalItems = new Integer((int)$r->readString());
            }

            if ($r->nodeType == $r::ELEMENT && $r->localName == 'event') {
                $currentEventCdbId = $r->getAttribute('cdbid');
            }

            if ($r->nodeType == $r::ELEMENT && $r->localName == 'keyword') {
                $keyword = $r->readString();
                $currentEventIsUdb3Place = strcasecmp('udb3 place', $keyword) == 0;
            }

            if ($r->nodeType == $r::END_ELEMENT && $r->localName == 'event') {
                $iriGenerator = $currentEventIsUdb3Place ? $this->placeIriGenerator : $this->eventIriGenerator;

                if (!is_null($currentEventCdbId)) {
                    $items[] = array(
                        '@id' => $iriGenerator->iri($currentEventCdbId),
                        '@type' => $currentEventIsUdb3Place ? 'Place' : 'Event',
                    );
                }

                $currentEventCdbId = null;
                $currentEventIsUdb3Place = false;
            }
        }

        return new Results($items, $totalItems);
    }
}
