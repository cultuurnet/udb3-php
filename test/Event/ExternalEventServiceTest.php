<?php

namespace CultuurNet\UDB3\Event;

use CultuurNet\UDB3\ReadModel\JsonDocument;
use GuzzleHttp\Psr7\Response;
use Http\Client\HttpClient;
use PHPUnit_Framework_TestCase;
use PHPUnit_Framework_MockObject_MockObject;

class ExternalEventServiceTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var HttpClient|PHPUnit_Framework_MockObject_MockObject
     */
    protected $httpClient;

    /**
     * @var EventServiceInterface
     */
    protected $eventService;

    public function setUp()
    {
        $this->httpClient = $this->getMock(HttpClient::class);
        $this->eventService = new ExternalEventService($this->httpClient);
    }

    /**
     * @test
     */
    public function it_should_fetch_some_external_json_and_return_it_as_a_document_when_asking_for_an_event()
    {
        $encodedJsonEvent = file_get_contents(__DIR__ . '/samples/event_with_udb3_place.json');
        $response = new Response(200, ['Content-Type' => 'application/json'], $encodedJsonEvent);
        $eventId = 'http://culudb-silex.dev/event/e3604613-af01-4d2b-8cee-13ab61b89651';

        $this->httpClient
            ->method('sendRequest')
            ->willReturn($response);

        $actualDocument = $this->eventService->getEvent($eventId);
        $expectedDocument = new JsonDocument($eventId, $encodedJsonEvent);

        $this->assertEquals($expectedDocument, $actualDocument);
    }
}
