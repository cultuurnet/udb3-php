<?php

namespace CultuurNet\UDB3\SavedSearches;

use CultuurNet\Auth\TokenCredentials;
use CultuurNet\UDB3\UDB2\Consumer;

/**
 * @deprecated Unused since III-2779.
 */
class SavedSearchesServiceFactory implements SavedSearchesServiceFactoryInterface
{
    /**
     * @var Consumer
     */
    private $consumer;

    /**
     * @param Consumer $consumer
     */
    public function __construct(Consumer $consumer)
    {
        $this->consumer = $consumer;
    }

    /**
     * @inheritdoc
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

        $cultureFeed = new \CultureFeed($oauthClient);
        return new \CultureFeed_SavedSearches_Default($cultureFeed);
    }
}
