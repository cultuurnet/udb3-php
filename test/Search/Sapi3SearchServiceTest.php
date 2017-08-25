<?php

namespace CultuurNet\UDB3\Search;

use CultuurNet\UDB3\Offer\IriOfferIdentifier;
use CultuurNet\UDB3\Offer\IriOfferIdentifierFactory;
use CultuurNet\UDB3\Offer\IriOfferIdentifierFactoryInterface;
use CultuurNet\UDB3\Offer\OfferIdentifierCollection;
use CultuurNet\UDB3\Offer\OfferType;
use Guzzle\Http\QueryString;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Uri;
use GuzzleHttp\Psr7\Response;
use Http\Client\HttpClient;
use ValueObjects\Number\Integer;
use ValueObjects\Web\Url;

class Sapi3SearchServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var HttpClient|\PHPUnit_Framework_MockObject_MockObject
     */
    private $httpClient;

    /**
     * @var IriOfferIdentifierFactoryInterface
     */
    private $offerIdentifier;

    public function setUp()
    {
        $this->httpClient = $this->createMock(HttpClient::class);
        $this->offerIdentifier = new IriOfferIdentifierFactory(
            'https?://udb-silex\.dev/(?<offertype>[event|place]+)/(?<offerid>[a-zA-Z0-9\-]+)'
        );
    }

    /**
     * @test
     */
    public function it_should_fetch_search_results_from_sapi_3()
    {
        $searchLocation =  new Uri('http://udb-search.dev/offers/');

        $searchService = new Sapi3SearchService($searchLocation, $this->httpClient, $this->offerIdentifier);
        $searchResponse = new Response(200, [], file_get_contents(__DIR__ . '/search-response.json'));

        $query = QueryString::fromString('q=modified:[2016-08-24T00:00:00+02:00 TO *]&start=0&limit=30');

        $expectedRequest = new Request(
            'GET',
            $searchLocation->withQuery((string) $query)
        );

        $this->httpClient
            ->expects($this->once())
            ->method('sendRequest')
            ->with($expectedRequest)
            ->willReturn($searchResponse);

        $expectedResults = new Results(
            OfferIdentifierCollection::fromArray([
                new IriOfferIdentifier(
                    Url::fromNative('http://udb-silex.dev/place/c90bc8d5-11c5-4ae3-9bf9-cce0969fdc56'),
                    'c90bc8d5-11c5-4ae3-9bf9-cce0969fdc56',
                    OfferType::PLACE()
                ),
                new IriOfferIdentifier(
                    Url::fromNative('http://udb-silex.dev/event/c54b1323-0928-402f-9419-16d7acd44d36'),
                    'c54b1323-0928-402f-9419-16d7acd44d36',
                    OfferType::EVENT()
                ),
            ]),
            Integer::fromNative(2)
        );

        $results = $searchService->search('modified:[2016-08-24T00:00:00+02:00 TO *]');

        $this->assertEquals($expectedResults, $results);
    }
}
