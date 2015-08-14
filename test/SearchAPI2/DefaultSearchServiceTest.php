<?php

namespace SearchAPI2;

use CultuurNet\Auth\ConsumerCredentials;
use CultuurNet\Auth\Guzzle\HttpClientFactory;
use CultuurNet\Auth\TokenCredentials;
use CultuurNet\Search\Parameter\FilterQuery;
use CultuurNet\Search\Parameter\Group;
use CultuurNet\Search\Parameter\Query;
use CultuurNet\UDB3\SearchAPI2\DefaultSearchService;
use Guzzle\Http\ClientInterface;
use Guzzle\Http\Message\RequestInterface;
use Guzzle\Http\Message\Response;
use Guzzle\Http\QueryString;

class DefaultSearchServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ConsumerCredentials
     */
    protected $consumerCredentials;

    /**
     * @var DefaultSearchService
     */
    protected $searchService;

    protected $baseUrl = 'http://acc.uitid.be/uitid/rest/searchv2';

    /**
     * @var HttpClientFactory | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $clientFactory;

    public function setUp()
    {
        $this->consumerCredentials = new ConsumerCredentials();
        $this->searchService = new DefaultSearchService(
            $this->baseUrl,
            $this->consumerCredentials
        );
        $this->clientFactory = $this->getMock(HttpClientFactory::class);
    }

    /**
     * @test
     */
    public function it_is_initializable_with_at_least_a_base_url_and_consumer_credentials()
    {
        $this->assertInstanceOf(DefaultSearchService::class, $this->searchService);
    }

    /**
     * @test
     */
    public function it_is_initializable_with_additional_token_credentials()
    {
        $tokenCredentials = new TokenCredentials('token', 'secret');

        $this->searchService = new DefaultSearchService(
            $this->baseUrl,
            $this->consumerCredentials,
            $tokenCredentials
        );

        $this->assertInstanceOf(DefaultSearchService::class, $this->searchService);
    }

    /**
     * @test
     */
    public function it_searches_with_an_array_of_arbitrary_solr_parameters()
    {
        $this->searchService->setHttpClientFactory($this->clientFactory);

        $queryString = $this->getMock(QueryString::class);
        $queryString
            ->expects($this->exactly(5))
            ->method('add')
            ->withConsecutive(
                ['q', 'pop'],
                ['fq', 'type:event'],
                ['group', 'true'],
                ['past', 'true'],
                ['unavailable', 'true']
            );

        $request = $this->getMock(RequestInterface::class);
        $request
            ->expects($this->once())
            ->method('getQuery')
            ->willReturn($queryString);

        $client = $this->getMock(ClientInterface::class);
        $client
            ->expects($this->once())
            ->method('get')
            ->with('search')
            ->willReturn($request);

        $this->clientFactory
            ->expects($this->once())
            ->method('createClient')
            ->willReturn($client);

        $request
            ->expects($this->once())
            ->method('send');

        $this->searchService->search([
            new Query('pop'),
            new FilterQuery('type:event'),
            new Group()
        ]);
    }

    /**
     * @test
     */
    public function it_returns_the_raw_search_response()
    {
        $this->searchService->setHttpClientFactory($this->clientFactory);
        $expectedResponse = new Response(200);

        $request = $this->getMock(RequestInterface::class);
        $request
            ->expects($this->once())
            ->method('getQuery')
            ->willReturn(new QueryString());

        $client = $this->getMock(ClientInterface::class);
        $client
            ->expects($this->once())
            ->method('get')
            ->with('search')
            ->willReturn($request);

        $this->clientFactory
            ->expects($this->once())
            ->method('createClient')
            ->willReturn($client);

        $request
            ->expects($this->once())
            ->method('send')
            ->willReturn($expectedResponse);

        $response = $this->searchService->search([]);
        $this->assertEquals($expectedResponse, $response);
    }
}
