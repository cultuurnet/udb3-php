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
        $dom = $this->loadDOM();
        $childNodes = $dom->documentElement->childNodes;
        $eventElement = $childNodes->item(0);

        // $eventElement is some node of some other document
        $temp_document = new DOMDocument('1.0', 'utf-8');
        $temp_document->appendChild($temp_document->importNode($eventElement, true));
        $eventXml = $temp_document->saveXML();

        return $eventXml;
    }

    /**
     * @return DOMDocument
     */
    private function loadDOM()
    {
        $dom = new DOMDocument('1.0', 'utf-8');
        $dom->preserveWhiteSpace = false;
        $dom->loadXML($this->value);

        return $dom;
    }

    /**
     * @return \DOMElement
     */
    public function eventElement()
    {
        $dom = $this->loadDOM();

        $xpath = new DOMXPath($dom);
        $xpath->registerNamespace('cdb', $dom->documentElement->namespaceURI);
        $elements = $xpath->query('//cdb:event');
        if ($elements->length >= 1) {
            /** @var \DOMElement $element */
            $element = $elements->item(0);
            return $element;
        } else {
            throw new \LogicException(
                'Unable to find cdbxml event element in the xml string.'
            );
        }
    }

    public function withCdbidAttribute($eventid)
    {
        $dom = $this->loadDOM();

        $xpath = new DOMXPath($dom);
        $xpath->registerNamespace('cdb', $dom->documentElement->namespaceURI);
        $elements = $xpath->query('//cdb:event');
        if ($elements->length >= 1) {
            /** @var \DOMElement $element */
            $element = $elements->item(0);
            $element->setAttribute('cdbid', $eventid);
        } else {
            throw new \LogicException(
                'Unable to find cdbxml event element in the xml string.'
            );
        }

        $xmlWithCdbid = $dom->saveXML();

        return new EventXmlString($xmlWithCdbid);
    }
}
