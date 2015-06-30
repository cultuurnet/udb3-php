<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\UDB2;

use CultuurNet\Auth\ConsumerCredentials;
use CultuurNet\Auth\Guzzle\OAuthProtectedService;
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
     * @param string $baseUrl
     * @param ConsumerCredentials $consumerCredentials
     * @param String $userId
     */
    public function __construct(
        $baseUrl,
        ConsumerCredentials $consumerCredentials,
        String $userId
    ) {
        parent::__construct(
            $baseUrl,
            $consumerCredentials
        );

        $this->userId = $userId;
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
     * @param string $eventId
     * @return string
     * @throws EventNotFoundException If the event can not be found.
     */
    public function getCdbXmlOfEvent($eventId)
    {
        $this->guardEventId($eventId);

        $request = $this->getClient()->get('event/' . $eventId);
        $response = $request->send();

        // @todo verify response Content-Type

        // @todo catch a 404 and throw EventNotFoundException instead
        $result = $response->getBody(true);

        return $result;
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
}
