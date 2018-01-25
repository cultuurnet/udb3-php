<?php

namespace CultuurNet\UDB3\Event;

use Broadway\CommandHandling\CommandBusInterface;
use Broadway\Domain\DomainMessage;
use Broadway\EventHandling\EventListenerInterface;
use CultuurNet\UDB3\Address\CultureFeedAddressFactoryInterface;
use CultuurNet\UDB3\Cdb\EventItemFactory;
use CultuurNet\UDB3\Event\Commands\UpdateGeoCoordinatesFromAddress;
use CultuurNet\UDB3\Event\Events\EventImportedFromUDB2;
use CultuurNet\UDB3\Event\Events\EventUpdatedFromUDB2;
use Psr\Log\LoggerInterface;

class GeoCoordinatesProcessManager implements EventListenerInterface
{
    /**
     * @var CommandBusInterface
     */
    private $commandBus;

    /**
     * @var CultureFeedAddressFactoryInterface
     */
    private $addressFactory;

    /**
     * @param CommandBusInterface $commandBus
     * @param CultureFeedAddressFactoryInterface $addressFactory
     */
    public function __construct(
        CommandBusInterface $commandBus,
        CultureFeedAddressFactoryInterface $addressFactory
    ) {
        $this->commandBus = $commandBus;
        $this->addressFactory = $addressFactory;
    }

    /**
     * @return array
     */
    private function getEventHandlers()
    {
        return [
            EventImportedFromUDB2::class => 'handleEventImportedFromUDB2',
            EventUpdatedFromUDB2::class => 'handleEventUpdatedFromUDB2',
        ];
    }

    /**
     * @param DomainMessage $domainMessage
     *
     * @uses handleEventImportedFromUDB2
     * @uses handleEventUpdatedFromUDB2
     */
    public function handle(DomainMessage $domainMessage)
    {
        $payload = $domainMessage->getPayload();
        $className = get_class($payload);
        $eventHandlers = $this->getEventHandlers();

        if (isset($eventHandlers[$className])) {
            $eventHandler = $eventHandlers[$className];
            call_user_func([$this, $eventHandler], $payload);
        }
    }

    /**
     * @param EventImportedFromUDB2 $eventImportedFromUDB2
     * @throws \CultureFeed_Cdb_ParseException
     */
    private function handleEventImportedFromUDB2(
        EventImportedFromUDB2 $eventImportedFromUDB2
    ) {
        $this->dispatchCommand(
            $eventImportedFromUDB2->getCdbXmlNamespaceUri(),
            $eventImportedFromUDB2->getCdbXml(),
            $eventImportedFromUDB2->getEventId()
        );
    }

    /**
     * @param EventUpdatedFromUDB2 $eventUpdatedFromUDB2
     * @throws \CultureFeed_Cdb_ParseException
     */
    private function handleEventUpdatedFromUDB2(
        EventUpdatedFromUDB2 $eventUpdatedFromUDB2
    ) {
        $this->dispatchCommand(
            $eventUpdatedFromUDB2->getCdbXmlNamespaceUri(),
            $eventUpdatedFromUDB2->getCdbXml(),
            $eventUpdatedFromUDB2->getEventId()
        );
    }

    /**
     * @param string $cdbXmlNamespaceUri
     * @param string $cdbXml
     * @param string $eventId
     * @throws \CultureFeed_Cdb_ParseException
     */
    private function dispatchCommand(
        $cdbXmlNamespaceUri,
        $cdbXml,
        $eventId
    ) {
        $event = EventItemFactory::createEventFromCdbXml(
            $cdbXmlNamespaceUri,
            $cdbXml
        );

        // Location is required, else the create would fail.
        $location = $event->getLocation();
        if ($location->getCdbid() || $location->getExternalId()) {
            return;
        }

        // Address is required, else the create would fail.
        $physicalAddress = $location->getAddress()->getPhysicalAddress();
        if (!$physicalAddress) {
            return;
        }

        // Address is always valid, else the create would fail.
        $address = $this->addressFactory->fromCdbAddress($physicalAddress);

        $command = new UpdateGeoCoordinatesFromAddress(
            $eventId,
            $address
        );

        $this->commandBus->dispatch($command);
    }
}
