<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\UDB2\AMQP;

use Broadway\Domain\DateTime;
use Broadway\Domain\DomainEventStream;
use Broadway\Domain\DomainMessage;
use Broadway\Domain\Metadata;
use Broadway\EventHandling\EventBusInterface;
use CultuurNet\Deserializer\DeserializerLocatorInterface;
use CultuurNet\Deserializer\DeserializerNotFoundException;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Rhumsaa\Uuid\Uuid;
use ValueObjects\String\String;

/**
 * Forwards messages coming in via AMQP to an event bus.
 */
class EventBusForwardingConsumer implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @var AMQPStreamConnection
     */
    private $connection;

    /**
     * @var string
     */
    private $queueName;

    /**
     * @var string
     */
    private $consumerTag;

    /**
     * @var EventBusInterface
     */
    private $eventBus;

    /**
     * @var DeserializerLocatorInterface
     */
    private $deserializerLocator;

    /**
     * @var AMQPChannel
     */
    private $channel;

    /**
     * @param AMQPStreamConnection $connection
     * @param EventBusInterface $eventBus
     */
    public function __construct(
        AMQPStreamConnection $connection,
        EventBusInterface $eventBus,
        DeserializerLocatorInterface $deserializerLocator
    ) {
        $this->connection = $connection;
        $this->channel = $connection->channel();

        $this->eventBus = $eventBus;

        $this->deserializerLocator = $deserializerLocator;

        $this->queueName = 'udb3.q.udb2';
        $this->consumerTag = 'udb3';

        $this->declareQueue();
        $this->registerConsumeCallback();
    }

    public function consume(AMQPMessage $message)
    {
        if ($this->logger) {
            $this->logger->info(
                'received message with content-type ' . $message->get(
                    'content_type'
                )
            );
        }

        $contentType = new String($message->get('content_type'));

        try {
            $deserializer = $this->deserializerLocator->getDeserializerForContentType(
                $contentType
            );
            $event = $deserializer->deserialize(
                new String($message->body)
            );

            $events = [
                new DomainMessage(
                    'foo',
                    0,
                    new Metadata(),
                    $event,
                    DateTime::now()
                ),
            ];

            $stream = new DomainEventStream($events);

            if ($this->logger) {
                $this->logger->info('passing on message to event bus');
            }

            $this->eventBus->publish(
                $stream
            );

        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->error($e->getMessage());
            }

            $message->delivery_info['channel']->basic_reject(
                $message->delivery_info['delivery_tag'],
                false
            );

            if ($this->logger) {
                $this->logger->info('message rejected');
            }

            return;
        }

        $message->delivery_info['channel']->basic_ack(
            $message->delivery_info['delivery_tag']
        );

        if ($this->logger) {
            $this->logger->info('message acknowledged');
        }
    }

    protected function declareQueue()
    {
        $this->channel->queue_declare(
            $this->queueName,
            $passive = false,
            $durable = true,
            $exclusive = false,
            $autoDelete = false
        );

        $this->channel->queue_bind(
            $this->queueName,
            'udb2',
            $routingKey = '#'
        );
    }

    protected function registerConsumeCallback()
    {
        $this->channel->basic_consume(
            $this->queueName,
            $consumerTag = $this->consumerTag,
            $noLocal = false,
            $noAck = false,
            $exclusive = false,
            $noWait = false,
            [$this, 'consume']
        );
    }
}
