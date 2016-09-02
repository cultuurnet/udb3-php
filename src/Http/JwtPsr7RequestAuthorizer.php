<?php

namespace CultuurNet\UDB3\Http;

use Lcobucci\JWT\Token as Jwt;
use Psr\Http\Message\RequestInterface;

class JwtPsr7RequestAuthorizer implements Psr7RequestAuthorizerInterface
{
    /**
     * @var Jwt
     */
    private $jwt;

    /**
     * @param Jwt $jwt
     */
    public function __construct(Jwt $jwt)
    {
        $this->jwt = $jwt;
    }

    /**
     * @param RequestInterface $request
     * @return RequestInterface
     */
    public function authorize(RequestInterface $request)
    {
        return $request->withHeader("Authorization", "Bearer {$this->jwt}");
    }
}
