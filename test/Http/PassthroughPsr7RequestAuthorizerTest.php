<?php

namespace CultuurNet\UDB3\Http;

use GuzzleHttp\Psr7\Request;

class PassthroughPsr7RequestAuthorizerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_can_pass_through_a_request_without_modification()
    {
        $authorizer = new PassthroughPsr7RequestAuthorizer();

        $request = new Request('DELETE', 'http://foo.bar');
        $authorizedRequest = $authorizer->authorize($request);

        $this->assertEquals($request, $authorizedRequest);
    }
}