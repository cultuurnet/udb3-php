<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\UDB2;

use CultuurNet\Auth\ConsumerCredentials;
use CultuurNet\Auth\Guzzle\OAuthProtectedService;
use Guzzle\Http\Exception\ClientErrorResponseException;
use ValueObjects\String\String;

/**
 * Service to retrieve event CDBXML, from the Entry API.
 *
 * This uses the 'Light UiTID' authentication, as described in
 * https://docs.google.com/document/d/14vteMLuhDbUbn_49WMoGxHXtGJIpMaD7fqoR7VyQDwI/edit#.
 */
class EventCdbXmlFromEntryAPI extends OAuthProtectedService implements EventCdbXmlServiceInterface
{
    /**
     * @var String
     */
    private $userId;

    /**
     * @var string
     */
    private $cdbXmlNamespaceUri;

    /**
     * @param string $baseUrl
     * @param ConsumerCredentials $consumerCredentials
     * @param String $userId
     * @param string $cdbXmlNamespaceUri
     */
    public function __construct(
        $baseUrl,
        ConsumerCredentials $consumerCredentials,
        String $userId,
        $cdbXmlNamespaceUri = 'http://www.cultuurdatabank.com/XMLSchema/CdbXSD/3.2/FINAL'
    ) {
        parent::__construct(
            $baseUrl,
            $consumerCredentials
        );

        $this->userId = $userId;
        $this->cdbXmlNamespaceUri = $cdbXmlNamespaceUri;
    }

    /**
     * @inheritdoc
     */
    protected function getClient(array $additionalOAuthParameters = array())
    {
        $client = parent::getClient($additionalOAuthParameters);
        $client->setDefaultOption(
            'headers',
            [
                'Accept' => 'text/xml'
            ]
        );
        $client->setDefaultOption(
            'query',
            [
                'uid' => (string) $this->userId
            ]
        );

        return $client;
    }

    /**
     * @inheritdoc
     */
    public function getCdbXmlOfEvent($eventId)
    {
        $this->guardEventId($eventId);

        $request = $this->getClient()->get('event/' . $eventId);

        try {
            $response = $request->send();
        } catch (ClientErrorResponseException $e) {
            if ($e->getResponse()->getStatusCode() == '404') {
                throw new EventNotFoundException(
                    "Event with cdbid '{$eventId}' could not be found via Entry API."
                );
            }

            throw $e;
        }

        // @todo verify response Content-Type

        $cdbXml = $response->getBody(true);
        return $this->extractEventElement($cdbXml, $eventId);
    }

    /**
     * @param string $cdbXml
     * @param string $eventId
     * @return string
     * @throws \RuntimeException
     */
    private function extractEventElement($cdbXml, $eventId)
    {
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
                        return $dom->saveXML();
                    }
            }
        }

        throw new \RuntimeException(
            "Event with cdbid '{$eventId}' could not be found in the Entry API response body."
        );
    }

    private function guardEventId($eventId)
    {
        if (!is_string($eventId)) {
            throw new \InvalidArgumentException(
                'Expected $eventId to be a string, received value of type ' . gettype($eventId)
            );
        }

        if ('' == trim($eventId)) {
            throw new \InvalidArgumentException('$eventId should not be empty');
        }
    }

    /**
     * @inheritdoc
     */
    public function getCdbXmlNamespaceUri()
    {
        return $this->cdbXmlNamespaceUri;
    }
}
