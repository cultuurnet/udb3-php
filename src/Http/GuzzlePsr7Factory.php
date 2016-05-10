<?php

namespace CultuurNet\UDB3\Http;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Uri;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;
use ValueObjects\String\String as StringLiteral;

class GuzzlePsr7Factory implements Psr7FactoryInterface
{
    /**
     * @var StringLiteral
     */
    private $authorizationHeader;

    /**
     * @param StringLiteral $authorizationHeader
     */
    public function __construct(StringLiteral $authorizationHeader)
    {
        $this->authorizationHeader = $authorizationHeader;
    }

    /**
     * @param string $method
     * @param UriInterface $uri
     * @param array $headers
     * @param string|null $body
     * @param string $protocolVersion
     * @return Request
     */
    public function createRequest(
        $method,
        UriInterface $uri,
        array $headers = [],
        $body = null,
        $protocolVersion = '1.1'
    ) {
        return new Request($method, $uri, $headers, $body, $protocolVersion);
    }

    /**
     * @param RequestInterface $request
     * @return RequestInterface
     */
    public function authorizeRequest(
        RequestInterface $request
    ) {
        return $request
            ->withHeader('Authorization', $this->authorizationHeader);
    }

    /**
     * @param string $uri
     * @return Uri
     */
    public function createUri($uri)
    {
        return new Uri($uri);
    }

    /**
     * @param string $content
     * @return StreamInterface
     */
    public function createContentStream($content)
    {
        return \GuzzleHttp\Psr7\stream_for($content);
    }
}
