<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Monolog;


use Monolog\Logger;
use SocketIO\Emitter;

class SocketIOEmitterHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SocketIOEmitterHandler
     */
    protected $handler;

    /**
     * @var Emitter|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $emitter;

    public function setUp()
    {
        // SocketIO\Emitter unfortunately does not adhere to an interface, so
        // we need to use the implementation and ensure all required
        // constructor arguments are provided.
        $this->emitter = $this->getMock(
            '\\SocketIO\\Emitter',
            [],
            [new TestRedisClientDouble()]
        );

        $this->handler = new SocketIOEmitterHandler($this->emitter);
    }

    /**
     * @return array Record
     */
    protected function getRecord(
        $level = Logger::WARNING,
        $message = 'test',
        $context = array()
    ) {
        return array(
            'message' => $message,
            'context' => $context,
            'level' => $level,
            'level_name' => Logger::getLevelName($level),
            'channel' => 'test',
            'datetime' => \DateTime::createFromFormat(
                'U.u',
                sprintf('%.6F', microtime(true))
            ),
            'extra' => array(),
        );
    }

    /**
     * @test
     */
    public function it_emits_to_the_socketIOEmitter()
    {
        $context = ['job_id' => 1];

        $this->emitter->expects($this->once())
            ->method('emit')
            ->with('job_started', $context);

        $record = $this->getRecord(Logger::WARNING, 'job_started', $context);
        $this->handler->handle($record);
    }
}

class TestRedisClientDouble
{
    public function publish()
    {

    }
}
