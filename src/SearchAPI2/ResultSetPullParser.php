<?php

namespace CultuurNet\UDB3\SearchAPI2;

use CultuurNet\UDB3\Iri\IriGeneratorInterface;
use CultuurNet\UDB3\Offer\IriOfferIdentifier;
use CultuurNet\UDB3\Offer\OfferIdentifierCollection;
use CultuurNet\UDB3\Offer\OfferType;
use CultuurNet\UDB3\Search\Results;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;
use ValueObjects\Number\Integer;
use ValueObjects\Web\Url;

/**
 * Parser using XML pull parsing to extract the ids from the CDBXML-formatted
 * results returned by Search API 2.
 */
class ResultSetPullParser implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    const OFFER_TYPE_EVENT = 'offer.event';
    const OFFER_TYPE_PLACE = 'offer.place';
    const OFFER_TYPE_UNKNOWN = 'offer.unknown';

    const CDBXML_TYPE_EVENT = 'cdbxml.event';
    const CDBXML_TYPE_ACTOR = 'cdbxml.actor';
    const CDBXML_TYPE_UNKNOWN = 'cdbxml.unknown';

    /**
     * @var array
     */
    private $knownCdbXmlResultTypes;

    /**
     * @var \XMLReader
     */
    protected $xmlReader;

    /**
     * @var IriGeneratorInterface[]
     */
    protected $fallbackIriGenerators;

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

        $this->fallbackIriGenerators = [
            self::OFFER_TYPE_EVENT => $eventIriGenerator,
            self::OFFER_TYPE_PLACE => $placeIriGenerator,
        ];

        $this->knownCdbXmlResultTypes = ['event', 'actor'];

        $this->logger = new NullLogger();
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
        $items = new OfferIdentifierCollection();
        $totalItems = $cdbId = $elementName = $offerType = $resultXmlString = null;

        $resetCurrentResultValues = function () use (&$cdbId, &$elementName, &$offerType, &$resultXmlString) {
            $cdbId = null;
            $elementName = self::CDBXML_TYPE_UNKNOWN;
            $offerType = self::OFFER_TYPE_UNKNOWN;
            $resultXmlString = '';
        };

        $resetCurrentResultValues();

        $r = $this->xmlReader;
        $r->xml($cdbxml);

        while ($r->read()) {
            if ($this->xmlNodeIsNumberOfRecordsTag($r)) {
                $totalItems = new Integer((int) $r->readString());
            }

            if ($this->xmlNodeIsResultOpeningTag($r)) {
                $resultXmlString = $r->readOuterXml();
                $cdbId = $r->getAttribute('cdbid');
                $elementName = 'cdbxml.' . $r->localName;

                if ($elementName == self::CDBXML_TYPE_EVENT) {
                    $offerType = self::OFFER_TYPE_EVENT;
                }
            }

            if ($this->xmlNodeIsUdb3PlaceKeyword($r) && $elementName == self::CDBXML_TYPE_EVENT) {
                $offerType = self::OFFER_TYPE_PLACE;
            }

            if ($this->xmlNodeIsLocationCategory($r) && $elementName == self::CDBXML_TYPE_ACTOR) {
                $offerType = self::OFFER_TYPE_PLACE;
            }

            if ($this->xmlNodeIsResultClosingTag($r)) {
                if ($offerType == self::OFFER_TYPE_UNKNOWN) {
                    // Skip if we haven't been able to deduce an offer type.
                    // (Eg. actor, but without the place category.)
                    continue;
                }

                if (empty($cdbId)) {
                    // Skip if no cdbid found.
                    continue;
                }

                // Null if attribute not set, empty string if not found in the search index.
                $externalUrl = $r->getAttribute('externalurl');
                if (empty($externalUrl)) {
                    $iriGenerator = $this->fallbackIriGenerators[$offerType];
                    $externalUrl = $iriGenerator->iri($cdbId);

                    $this->logger->debug(
                        "Created fallback url for search result {$cdbId}, with cdbxml: {$resultXmlString}"
                    );
                }

                $items = $items->with(
                    new IriOfferIdentifier(
                        Url::fromNative($externalUrl),
                        $cdbId,
                        $this->getOfferTypeEnum($offerType)
                    )
                );

                $resetCurrentResultValues();
            }
        }

        return new Results($items, $totalItems);
    }

    /**
     * @param \XMLReader $r
     * @return bool
     */
    private function xmlNodeIsNumberOfRecordsTag(\XMLReader $r)
    {
        return $r->nodeType == $r::ELEMENT && $r->localName == 'nofrecords';
    }

    /**
     * @param \XMLReader $r
     * @return bool
     */
    private function xmlNodeIsResultOpeningTag(\XMLReader $r)
    {
        return $r->nodeType == $r::ELEMENT && $this->xmlNodeIsKnownResultType($r);
    }

    /**
     * @param \XMLReader $r
     * @return bool
     */
    private function xmlNodeIsResultClosingTag(\XMLReader $r)
    {
        return $r->nodeType == $r::END_ELEMENT && $this->xmlNodeIsKnownResultType($r);
    }

    /**
     * @param \XMLReader $r
     * @return bool
     */
    private function xmlNodeIsKnownResultType(\XMLReader $r)
    {
        return in_array($r->localName, $this->knownCdbXmlResultTypes);
    }

    /**
     * @param \XMLReader $r
     * @return bool
     */
    private function xmlNodeIsUdb3PlaceKeyword(\XMLReader $r)
    {
        return $r->nodeType == $r::ELEMENT && $r->localName == 'keyword' &&
            strcasecmp('udb3 place', $r->readString()) == 0;
    }

    /**
     * @param \XMLReader $r
     * @return bool
     */
    private function xmlNodeIsLocationCategory(\XMLReader $r)
    {
        return $r->nodeType == $r::ELEMENT && $r->localName == 'category' && $r->getAttribute('catid') == '8.15.0.0.0';
    }

    /**
     * @param string $offerTypeString
     * @return OfferType
     */
    private function getOfferTypeEnum($offerTypeString)
    {
        $parts = explode('.', $offerTypeString);
        $offerTypeString = $parts[count($parts) - 1];
        return OfferType::fromCaseInsensitiveValue($offerTypeString);
    }
}
