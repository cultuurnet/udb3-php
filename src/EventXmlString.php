<?php
/**
 * Created by PhpStorm.
 * User: nicolas
 * Date: 09/10/15
 * Time: 13:39
 */

namespace CultuurNet\UDB3;

use DOMDocument;
use DOMXPath;

class EventXmlString extends XmlString
{
    /**
     * @return string
     */
    public function toEventXmlString()
    {
        $dom = new DOMDocument('1.0', 'utf-8');
        $dom->preserveWhiteSpace = false;
        $dom->loadXML($this->value);
        $childNodes = $dom->documentElement->childNodes;
        $eventElement = $childNodes->item(0);

        // $eventElement is some node of some other document
        $temp_document = new DOMDocument('1.0', 'utf-8');
        $temp_document->appendChild($temp_document->importNode($eventElement, true));
        $eventXml = $temp_document->saveXML();

        return $eventXml;
    }

    public function withCdbidAttribute($eventid)
    {
        $dom = new DOMDocument('1.0', 'utf-8');
        $dom->preserveWhiteSpace = false;
        $dom->loadXML($this->value);

        $xpath = new DOMXPath($dom);
        $elements = $xpath->query('//cdbxml/event');
        if ($elements->length >= 1) {
            $element = $elements->item(0);
            $element->setAttribute('cdbid', $eventid);
        }
        $xmlWithCdbid = $dom->saveXML();

        return new EventXmlString($xmlWithCdbid);
    }
}
