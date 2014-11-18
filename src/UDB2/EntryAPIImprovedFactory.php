<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\UDB2;


use CultuurNet\Auth\TokenCredentials;

final class EntryAPIImprovedFactory
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
    )
    {
        $this->consumer = $consumer;
    }

    /**
     * @param TokenCredentials $tokenCredentials
     * @return EntryAPI
     */
    public function withTokenCredentials(TokenCredentials $tokenCredentials)
    {
        return new EntryAPI(
            $this->consumer->getTargetUrl(),
            $this->consumer->getConsumerCredentials(),
            $tokenCredentials
        );
    }
}

