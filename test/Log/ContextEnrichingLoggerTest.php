<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Log;

use Monolog\Logger;
use Psr\Log\LoggerInterface;

class ContextEnrichingLoggerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_passes_additional_context_to_the_decorated_logger()
    {
        /** @var LoggerInterface|\PHPUnit_Framework_MockObject_MockObject $decoratedLogger */
        $decoratedLogger = $this->createMock(LoggerInterface::class);
        $additionalContext = array(
            'job_id' => 1,
        );
        $logger = new ContextEnrichingLogger(
            $decoratedLogger,
            $additionalContext
        );

        $decoratedLogger->expects($this->once())
            ->method('log')
            ->with(
                Logger::DEBUG,
                'test',
                [
                    'foo' => 'bar',
                    'job_id' => 1
                ]
            );
        $logger->log(
            Logger::DEBUG,
            'test',
            [
                'foo' => 'bar'
            ]
        );
    }
}
