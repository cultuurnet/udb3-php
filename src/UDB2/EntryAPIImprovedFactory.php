<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\UDB2;

use CultuurNet\Auth\TokenCredentials;
use CultuurNet\Entry\EntryAPI;
use Guzzle\Log\ClosureLogAdapter;
use Guzzle\Plugin\Log\LogPlugin;

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
    ) {
        $this->consumer = $consumer;
    }

    /**
     * @param TokenCredentials $tokenCredentials
     * @return EntryAPI
     */
    public function withTokenCredentials(TokenCredentials $tokenCredentials)
    {
        $entryApi = new EntryAPI(
            $this->consumer->getTargetUrl(),
            $this->consumer->getConsumerCredentials(),
            $tokenCredentials
        );

        // Print request and response for debugging purposes.
        $adapter = new ClosureLogAdapter(
            function ($message, $priority, $extras) {
                // @todo handle $priority
                print $message;
            }
        );

        $format = "\n\n# Request:\n{request}\n\n# Response:\n{response}\n\n# Errors: {curl_code} {curl_error}\n\n";
        $log = new LogPlugin($adapter, $format);

        $entryApi->getHttpClientFactory()->addSubscriber($log);

        return $entryApi;
    }
}
