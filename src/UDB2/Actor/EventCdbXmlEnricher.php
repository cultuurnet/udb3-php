<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\UDB2\Actor;

use Broadway\Domain\DateTime;
use Broadway\Domain\DomainEventStream;
use Broadway\Domain\DomainMessage;
use Broadway\Domain\DomainMessageInterface;
use Broadway\Domain\Metadata;
use Broadway\EventHandling\EventBusInterface;
use Broadway\EventHandling\EventListenerInterface;
use CultuurNet\UDB2DomainEvents\ActorCreated;
use CultuurNet\UDB2DomainEvents\ActorUpdated;
use CultuurNet\UDB3\Cdb\ActorItemFactory;
use CultuurNet\UDB3\Cdb\DateTimeFactory;
use CultuurNet\UDB3\EventHandling\DelegateEventHandlingToSpecificMethodTrait;
use CultuurNet\UDB3\UDB2\Actor\Events\ActorCreatedEnrichedWithCdbXml;
use CultuurNet\UDB3\UDB2\Actor\Events\ActorUpdatedEnrichedWithCdbXml;
use CultuurNet\UDB3\UDB2\ActorCdbXmlServiceInterface;
use CultuurNet\UDB3\UDB2\ActorNotFoundException;
use CultuurNet\UDB3\UDB2\OutdatedXmlRepresentationException;
use DateTimeInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use ValueObjects\Identity\UUID;
use ValueObjects\String\String;

/**
 * Creates new event messages based on incoming UDB2 events, enriching them with
 * cdb xml so other components do not need to take care of that themselves.
 */
class EventCdbXmlEnricher implements EventListenerInterface, LoggerAwareInterface
{
    use DelegateEventHandlingToSpecificMethodTrait;
    use LoggerAwareTrait;

    /**
     * @var ActorCdbXmlServiceInterface
     */
    protected $cdbXmlService;

    /**
     * @var EventBusInterface
     */
    protected $eventBus;

    /**
     * @var array
     */
    protected $logContext;

    /**
     * @param ActorCdbXmlServiceInterface $cdbXmlService
     * @param EventBusInterface $eventBus
     */
    public function __construct(
        ActorCdbXmlServiceInterface $cdbXmlService,
        EventBusInterface $eventBus
    ) {
        $this->cdbXmlService = $cdbXmlService;
        $this->eventBus = $eventBus;
    }

    /**
     * @param DomainMessageInterface $domainMessage
     */
    private function setLogContextFromDomainMessage(
        DomainMessageInterface $domainMessage
    ) {
        $this->logContext = [];

        $metadata = $domainMessage->getMetadata()->serialize();
        if (isset($metadata['correlation_id'])) {
            $this->logContext['correlation_id'] = $metadata['correlation_id'];
        }
    }

    /**
     * @param ActorCreated $actorCreated
     * @param DomainMessageInterface $message
     */
    private function applyActorCreated(
        ActorCreated $actorCreated,
        DomainMessageInterface $message
    ) {
        $this->setLogContextFromDomainMessage($message);

        $xml = $this->getActorXml(
            $actorCreated->getActorId(),
            $actorCreated->getTime()
        );

        $enrichedActorCreated = ActorCreatedEnrichedWithCdbXml::fromActorCreated(
            $actorCreated,
            new String($xml),
            new String($this->cdbXmlService->getCdbXmlNamespaceUri())
        );

        $this->publish(
            $enrichedActorCreated,
            $message->getMetadata()
        );
    }

    /**
     * @param ActorUpdated $actorUpdated
     * @param DomainMessageInterface $message
     */
    private function applyActorUpdated(
        ActorUpdated $actorUpdated,
        DomainMessageInterface $message
    ) {
        $this->setLogContextFromDomainMessage($message);

        $xml = $this->getActorXml(
            $actorUpdated->getActorId(),
            $actorUpdated->getTime()
        );

        $enrichedActorUpdated = ActorUpdatedEnrichedWithCdbXml::fromActorUpdated(
            $actorUpdated,
            new String($xml),
            new String($this->cdbXmlService->getCdbXmlNamespaceUri())
        );

        $this->publish(
            $enrichedActorUpdated,
            $message->getMetadata()
        );
    }

    /**
     * @param object $payload
     * @param Metadata $metadata
     */
    private function publish($payload, Metadata $metadata)
    {
        $message = new DomainMessage(
            UUID::generateAsString(),
            1,
            $metadata,
            $payload,
            DateTime::now()
        );

        $domainEventStream = new DomainEventStream([$message]);
        $this->eventBus->publish($domainEventStream);
    }

    /**
     * @param String $actorId
     * @param DateTimeInterface $updatedSince
     *
     * @throws ActorNotFoundException
     * @throws OutdatedXmlRepresentationException
     *
     * @return String
     */
    private function getActorXml(String $actorId, DateTimeInterface $updatedSince)
    {
        $xml = $this->cdbXmlService->getCdbXmlOfActor((string)$actorId);
        $this->guardXmlFreshness($actorId, $xml, $updatedSince);

        return new String($xml);
    }

    /**
     * @param String $actorId
     * @param string $xml
     * @param DateTimeInterface $updatedSince
     *
     * @throws OutdatedXmlRepresentationException
     *
     * @return void
     */
    private function guardXmlFreshness($actorId, $xml, DateTimeInterface $updatedSince)
    {
        $actualUpdateDate = $this->getUpdatedDate($xml);

        if ($actualUpdateDate < $updatedSince) {
            $msg = 'The xml retrieved from UDB2 seems older than the time indicated in the event message.';

            $exception = new OutdatedXmlRepresentationException(
                $msg,
                (string)$actorId,
                $updatedSince,
                $actualUpdateDate
            );

            $this->logError(
                $msg,
                [
                    'actorId' => (string)$actorId,
                    'exception' => $exception,
                    'actualUpdateDate' => $actualUpdateDate->format(\DateTime::ISO8601),
                    'sinceDate' => $updatedSince->format(\DateTime::ISO8601)
                ]
            );

            throw $exception;
        }
    }

    private function logError($msg, $context = [])
    {
        if ($this->logger) {
            $this->logger->error($msg, $this->logContext + $context);
        }
    }

    /**
     * @param string $actorXml
     * @return \DateTimeImmutable
     */
    private function getUpdatedDate($actorXml)
    {
        $actor = ActorItemFactory::createActorFromCdbXml(
            $this->cdbXmlService->getCdbXmlNamespaceUri(),
            $actorXml
        );

        return DateTimeFactory::dateTimeFromDateString($actor->getLastUpdated());
    }
}
