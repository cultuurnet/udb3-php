<?php

namespace CultuurNet\UDB3\EventListener;

use Broadway\Domain\DomainEventStream;
use Broadway\Domain\DomainEventStreamInterface;
use Broadway\EventHandling\EventBusInterface;
use Broadway\EventHandling\EventListenerInterface;
use CultuurNet\UDB3\DomainMessage\DomainMessageEnricherInterface;

class EnrichingEventListenerDecorator implements EventBusInterface
{
    /**
     * @var EventBusInterface
     */
    private $decoratee;

    /**
     * @var DomainMessageEnricherInterface
     */
    private $enricher;

    /**
     * @param EventBusInterface $decoratee
     * @param DomainMessageEnricherInterface $enricher
     */
    public function __construct(
        EventBusInterface $decoratee,
        DomainMessageEnricherInterface $enricher
    ) {
        $this->decoratee = $decoratee;
        $this->enricher = $enricher;
    }

    /**
     * @inheritdoc
     */
    public function subscribe(EventListenerInterface $eventListener)
    {
        $this->decoratee->subscribe($eventListener);
    }

    /**
     * @inheritdoc
     */
    public function publish(DomainEventStreamInterface $stream)
    {
        $domainMessages = [];

        foreach ($stream->getIterator() as $domainMessage) {
            if ($this->enricher->supports($domainMessage)) {
                $domainMessage = $this->enricher->enrich($domainMessage);
            }

            $domainMessages[] = $domainMessage;
        }

        $this->decoratee->publish(
            new DomainEventStream($domainMessages)
        );
    }
}
