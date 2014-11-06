<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Monolog;


use Monolog\Handler\AbstractProcessingHandler;

class SocketIOEmitterHandler extends AbstractProcessingHandler
{
    /**
     * Writes the record down to the log of the implementing handler
     *
     * @param  array $record
     * @return void
     */
    protected function write(array $record)
    {
        $redis = new \Credis_Client();
        $emitter = new \SocketIO\Emitter($redis);
        $emitter->emit(
            'time',
            array(
                $record['formatted'],
                $record['context']
            )
        );
    }
} 
