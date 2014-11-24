<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\UDB2;

use CultuurNet\Auth\TokenCredentials;

final class EntryAPIFactory
{
    /**
     * @var Consumer
     */
    private $consumer;

    /**
     * @param Consumer $consumer
     */
    public function __construct(
        Consumer $consumer
    ) {
        $this->consumer = $consumer;
    }

    /**
     * @param TokenCredentials $tokenCredentials
     * @return \CultureFeed_EntryApi
     */
    public function withTokenCredentials(TokenCredentials $tokenCredentials)
    {
        $consumerCredentials = $this->consumer->getConsumerCredentials();

        $oauthClient = new \CultureFeed_DefaultOAuthClient(
            $consumerCredentials->getKey(),
            $consumerCredentials->getSecret(),
            $tokenCredentials->getToken(),
            $tokenCredentials->getSecret()
        );
        $oauthClient->setEndpoint($this->consumer->getTargetUrl());
        return new \CultureFeed_EntryApi($oauthClient);
    }
}
