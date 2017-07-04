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

    /**
     * @var string
     */
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
        $this->clientFactory = $this->createMock(HttpClientFactory::class);
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

        $queryString = $this->createMock(QueryString::class);
        $queryString
            ->expects($this->exactly(7))
            ->method('add')
            ->withConsecutive(
                ['q', 'pop'],
                ['fq', 'type:event OR (type:actor AND category_id:8.15.0.0.0)'],
                ['group', 'true'],
                ['version', '3.3'],
                ['past', 'true'],
                ['unavailable', 'true'],
                ['udb3filtering', 'false']
            );

        $request = $this->createMock(RequestInterface::class);
        $request
            ->expects($this->once())
            ->method('getQuery')
            ->willReturn($queryString);

        $client = $this->createMock(ClientInterface::class);
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
            new FilterQuery('type:event OR (type:actor AND category_id:8.15.0.0.0)'),
            new Group(),
        ]);
    }

    /**
     * @test
     */
    public function it_returns_the_raw_search_response()
    {
        $this->searchService->setHttpClientFactory($this->clientFactory);
        $expectedResponse = new Response(200);

        $request = $this->createMock(RequestInterface::class);
        $request
            ->expects($this->once())
            ->method('getQuery')
            ->willReturn(new QueryString());

        $client = $this->createMock(ClientInterface::class);
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
