<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\UDB2;

use CultuurNet\Auth\Guzzle\OAuthProtectedService;

class EventCdbXmlFromEntryAPI extends OAuthProtectedService implements EventCdbXmlServiceInterface
{
    /**
     * @return \Guzzle\Http\Client
     */
    protected function getClient()
    {
        $client = parent::getClient();
        $client->setDefaultOption(
            'headers',
            [
                'Accept' => 'text/xml'
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
        $request = $this->getClient()->get('event/' . $eventId);
        $response = $request->send();

        // @todo catch a 404 and throw EventNotFoundException instead

        $result = $response->getBody(true);

        return $result;
    }
}
