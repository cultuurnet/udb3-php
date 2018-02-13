<?php

namespace CultuurNet\UDB3\Http;

use Psr\Http\Message\RequestInterface;
use ValueObjects\StringLiteral\StringLiteral;

class ApiKeyPsr7RequestAuthorizer implements Psr7RequestAuthorizerInterface
{
    /**
     * @todo Is it possible to reuse ApiKey from 'cultuurnet/udb3-api-guard'?
     * @var StringLiteral
     */
    private $apiKey;

    /**
     * It is also possible to work without ApiKey, so the ApiKey can be null.
     * This can only be covered by setting default null value.
     *
     * @param StringLiteral $apiKey
     */
    public function __construct(StringLiteral $apiKey)
    {
        $this->apiKey = $apiKey;
    }

    /**
     * @inheritdoc
     */
    public function authorize(RequestInterface $request)
    {
        return $request->withHeader("X-Api-Key", "{$this->apiKey}");
    }
}
