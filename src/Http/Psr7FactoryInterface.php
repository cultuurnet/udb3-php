<?php

namespace CultuurNet\UDB3\Http;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;

interface Psr7FactoryInterface
{
    /**
     * @param string $method
     * @param UriInterface $uri
     * @return RequestInterface
     */
    public function createRequest($method, UriInterface $uri);

    /**
     * @param string $method
     * @param UriInterface $uri
     * @return RequestInterface
     */
    public function createAuthorizedRequest($method, UriInterface $uri);

    /**
     * @param string $uri
     * @return UriInterface
     */
    public function createUri($uri);

    /**
     * @param string $content
     * @return StreamInterface
     */
    public function createContentStream($content);
}
