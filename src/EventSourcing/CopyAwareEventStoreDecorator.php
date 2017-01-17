<?php

namespace CultuurNet\UDB3\EventSourcing;

use Broadway\Domain\DomainEventStream;
use Broadway\Domain\DomainEventStreamInterface;
use Broadway\Domain\DomainMessage;
use CultuurNet\UDB3\Event\Events\EventCopied;

class CopyAwareEventStoreDecorator extends AbstractEventStoreDecorator
{
    /**
     * @inheritdoc
     */
    public function load($id)
    {
        return $this->completeStream($this->eventStore->load($id));
    }

    private function completeStream(DomainEventStreamInterface $eventStream)
    {
        $events = iterator_to_array($eventStream);
        /** @var DomainMessage $oldestMessage */
        $oldestMessage = current($events);
        if ($oldestMessage->getPlayhead() === 0) {
            return $eventStream;
        }

        $parentId = $this->identifyParent($oldestMessage);
        $parentEventStream = $this->eventStore->load($parentId);

        $inheritedEvents = array_slice(iterator_to_array($parentEventStream), 0, $oldestMessage->getPlayhead());
        $combinedEvents = array_merge($inheritedEvents, $events);

        return $this->completeStream(new DomainEventStream($combinedEvents));
    }

    /**
     * @param DomainMessage $message
     * @return string
     */
    private function identifyParent(DomainMessage $message)
    {
        /** @var EventCopied $domainEvent */
        $domainEvent = $message->getPayload();
        return $domainEvent->getOriginalEventId();
    }
}
