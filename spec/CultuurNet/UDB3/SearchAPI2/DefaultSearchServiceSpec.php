<?php

namespace spec\CultuurNet\UDB3\SearchAPI2;

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
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * @mixin \CultuurNet\UDB3\SearchAPI2\DefaultSearchService
 */
class DefaultSearchServiceSpec extends ObjectBehavior
{
    const BASE_URL = 'http://acc.uitid.be/uitid/rest/searchv2';

    public function it_is_initializable_with_at_least_a_base_url_and_consumer_credentials(
        ConsumerCredentials $consumerCredentials
    ) {
        $this->beConstructedWith(
            self::BASE_URL,
            $consumerCredentials
        );
        $this->shouldHaveType(
            DefaultSearchService::class
        );
    }

    public function it_is_initializable_with_additional_token_credentials(
        ConsumerCredentials $consumerCredentials,
        TokenCredentials $tokenCredentials
    ) {
        $this->beConstructedWith(
            self::BASE_URL,
            $consumerCredentials,
            $tokenCredentials
        );
        $this->shouldHaveType(
            DefaultSearchService::class
        );
    }

    public function it_searches_with_an_array_of_arbitrary_solr_parameters(
        ConsumerCredentials $consumerCredentials,
        TokenCredentials $tokenCredentials,
        HttpClientFactory $clientFactory,
        ClientInterface $client,
        RequestInterface $request,
        QueryString $queryString
    ) {
        $baseUrl = self::BASE_URL;
        $this->beConstructedWith(
            $baseUrl,
            $consumerCredentials,
            $tokenCredentials
        );
        $this->setHttpClientFactory($clientFactory);

        $clientFactory
            ->createClient(
                $baseUrl,
                $consumerCredentials,
                $tokenCredentials
            )
            ->willReturn(
                $client
            );

        $client->get('search')->willReturn($request);
        $request->getQuery()->willReturn($queryString);
        $queryString->add('q', 'pop')->shouldBeCalled();
        $queryString->add('fq', 'type:event')->shouldBeCalled();
        $queryString->add('group', 'true')->shouldBeCalled();
        $queryString->add('unavailable', 'true')->shouldBeCalled();
        $queryString->add('past', 'true')->shouldBeCalled();

        $request->send()->shouldBeCalled();

        $this->search(
            array(
                new Query('pop'),
                new FilterQuery('type:event'),
                new Group(),
            )
        );
    }

    public function it_returns_the_raw_search_response(
        ConsumerCredentials $consumerCredentials,
        TokenCredentials $tokenCredentials,
        HttpClientFactory $clientFactory,
        ClientInterface $client,
        RequestInterface $request,
        QueryString $queryString,
        Response $response
    ) {
        $this->beConstructedWith(
            self::BASE_URL,
            $consumerCredentials,
            $tokenCredentials
        );

        $this->setHttpClientFactory($clientFactory);
        $clientFactory
            ->createClient(
                self::BASE_URL,
                $consumerCredentials,
                $tokenCredentials
            )
            ->willReturn($client);

        $client->get('search')->willReturn($request);
        $request->getQuery()->willReturn($queryString);
        $request
            ->send()
            ->willReturn($response);

        $this->search(array())->shouldReturn($response);
    }
}
