<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Monolog;

use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;
use SocketIO\Emitter;

class SocketIOEmitterHandler extends AbstractProcessingHandler
{
    /**
     * @var Emitter
     */
    protected $emitter;

    /**
     * @param Emitter $emitter
     * @param integer $level  The minimum logging level at which this handler will be triggered
     * @param Boolean $bubble Whether the messages that are handled can bubble up the stack or not
     */
    public function __construct(Emitter $emitter, $level = Logger::DEBUG, $bubble = true)
    {
        parent::__construct(
            $level,
            $bubble
        );

        $this->emitter = $emitter;
    }

    /**
     * {@inheritdoc}
     */
    protected function write(array $record)
    {
        $data = $record['context'];
        $event = $record['message'];

        $this->emitter->emit($event, $data);
    }
}
