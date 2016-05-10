<?php

namespace CultuurNet\UDB3\Offer;

use CultuurNet\UDB3\Http\GuzzlePsr7Factory;
use CultuurNet\UDB3\Label;
use Http\Client\HttpClient;
use Psr\Http\Message\RequestInterface;
use ValueObjects\String\String as StringLiteral;
use ValueObjects\Web\Url;

class DefaultExternalOfferEditingServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var HttpClient|\PHPUnit_Framework_MockObject_MockObject
     */
    private $httpClient;

    /**
     * @var StringLiteral
     */
    private $authorizationHeader;

    /**
     * @var GuzzlePsr7Factory
     */
    private $psr7Factory;

    /**
     * @var DefaultExternalOfferEditingService
     */
    private $service;

    public function setUp()
    {
        $this->httpClient = $this->getMock(HttpClient::class);

        $this->authorizationHeader = new StringLiteral('Bearer json.web.token');
        $this->psr7Factory = new GuzzlePsr7Factory($this->authorizationHeader);

        $this->service = new DefaultExternalOfferEditingService(
            $this->httpClient,
            $this->psr7Factory
        );
    }

    /**
     * @test
     */
    public function it_can_add_a_label_using_the_jsonld_api()
    {
        $iriOfferIdentifier = new IriOfferIdentifier(
            Url::fromNative('http://uitdatabank.be/event/123456'),
            '123456',
            OfferType::EVENT()
        );

        $label = new Label('foo');

        $expectedBody = '{"label":"foo"}';

        $request = null;

        $this->httpClient->expects($this->once())
            ->method('sendRequest')
            ->willReturnCallback(
                function (RequestInterface $requestArgument) use (&$request) {
                    // We can't do a strict comparison with the with() method
                    // because the actual request object would have a different
                    // content stream as body and could have additional headers
                    // that have not been set explicitly. So instead, put the
                    // request in a variable and do some other comparisons
                    // later.
                    $request = $requestArgument;
                }
            );

        $this->service->addLabel($iriOfferIdentifier, $label);

        /* @var RequestInterface $request */
        $this->assertEquals('POST', $request->getMethod());
        $this->assertEquals('http://uitdatabank.be/event/123456/labels', (string) $request->getUri());
        $this->assertEquals((string) $this->authorizationHeader, $request->getHeaderLine('Authorization'));
        $this->assertEquals('application/json', $request->getHeaderLine('Content-Type'));
        $this->assertEquals($expectedBody, $request->getBody()->getContents());
    }
}
