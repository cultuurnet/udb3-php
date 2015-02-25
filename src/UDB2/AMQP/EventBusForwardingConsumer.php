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
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Rhumsaa\Uuid\Uuid;
use ValueObjects\String\String;

/**
 * Forwards messages coming in via AMQP to an event bus.
 */
class EventBusForwardingConsumer
{
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
     * @param AMQPStreamConnection $connection
     * @param EventBusInterface $eventBus
     */
    public function __construct(
        AMQPStreamConnection $connection,
        EventBusInterface $eventBus,
        DeserializerLocatorInterface $deserializerLocator
    ) {
        $this->connection = $connection;
        $this->deserializerLocator = $deserializerLocator;

        $this->queueName = 'udb3.q.udb2';
        $this->consumerTag = 'udb3-' . $_SERVER['SERVER_NAME'];

        $this->declareQueue();
        $this->registerConsumeCallback();
    }

    public function consume(AMQPMessage $message)
    {
        $contentType = new String($message->get('content_type'));

        $deserializer = $this->deserializerLocator->getDeserializerForContentType($contentType);
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
        $this->eventBus->publish(
            $stream
        );
    }

    protected function declareQueue()
    {
        $passive = false;
        $durable = true;
        $exclusive = false;
        $autoDelete = false;

        $this->connection
            ->channel()
            ->queue_declare(
                $this->queueName,
                $passive,
                $durable,
                $exclusive,
                $autoDelete
            );
    }

    protected function registerConsumeCallback()
    {
        $this->connection
            ->channel()
            ->basic_consume(
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
