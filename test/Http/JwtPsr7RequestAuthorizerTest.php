<?php

namespace CultuurNet\UDB3\Http;

use GuzzleHttp\Psr7\Request;
use Lcobucci\JWT\Signature;
use Lcobucci\JWT\Token as Jwt;

class JwtPsr7RequestAuthorizerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_can_authorize_requests()
    {
        $jwt = new Jwt(
            ['alg' => 'mock'],
            ['foo' => 'bar'],
            new Signature('example'),
            ['jwt', 'mock', 'example']
        );

        $authorizer = new JwtPsr7RequestAuthorizer($jwt);

        $request = new Request('DELETE', 'http://foo.bar');
        $authorizedRequest = $authorizer->authorize($request);

        $this->assertEquals(
            'Bearer jwt.mock.example',
            $authorizedRequest->getHeaderLine('Authorization')
        );
    }
}
