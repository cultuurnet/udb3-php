<?php

namespace CultuurNet\UDB3\Place;

use Broadway\CommandHandling\CommandBusInterface;
use Broadway\Domain\DomainMessage;
use Broadway\EventHandling\EventListenerInterface;
use CultureFeed_Cdb_Data_Address;
use CultuurNet\UDB3\Actor\ActorImportedFromUDB2;
use CultuurNet\UDB3\Address\CultureFeedAddressFactoryInterface;
use CultuurNet\UDB3\Cdb\ActorItemFactory;
use CultuurNet\UDB3\Place\Commands\UpdateGeoCoordinatesFromAddress;
use CultuurNet\UDB3\Place\Events\MajorInfoUpdated;
use CultuurNet\UDB3\Place\Events\PlaceCreated;
use CultuurNet\UDB3\Place\Events\PlaceImportedFromUDB2;
use CultuurNet\UDB3\Place\Events\PlaceUpdatedFromUDB2;
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
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param CommandBusInterface $commandBus
     * @param CultureFeedAddressFactoryInterface $addressFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        CommandBusInterface $commandBus,
        CultureFeedAddressFactoryInterface $addressFactory,
        LoggerInterface $logger
    ) {
        $this->commandBus = $commandBus;
        $this->addressFactory = $addressFactory;
        $this->logger = $logger;
    }

    /**
     * @return array
     */
    private function getEventHandlers()
    {
        return [
            PlaceCreated::class => 'handlePlaceCreated',
            MajorInfoUpdated::class => 'handleMajorInfoUpdated',
            PlaceImportedFromUDB2::class => 'handleActorImportedFromUDB2',
            PlaceUpdatedFromUDB2::class => 'handleActorImportedFromUDB2',
        ];
    }

    /**
     * @param DomainMessage $domainMessage
     *
     * @uses handlePlaceCreated
     * @uses handleMajorInfoUpdated
     * @uses handleActorImportedFromUDB2
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
     * @param PlaceCreated $placeCreated
     */
    private function handlePlaceCreated(PlaceCreated $placeCreated)
    {
        $command = new UpdateGeoCoordinatesFromAddress(
            $placeCreated->getPlaceId(),
            $placeCreated->getAddress()
        );

        $this->commandBus->dispatch($command);
    }

    /**
     * @param MajorInfoUpdated $majorInfoUpdated
     */
    private function handleMajorInfoUpdated(MajorInfoUpdated $majorInfoUpdated)
    {
        // We don't know if the address has actually been updated because
        // MajorInfoUpdated is too coarse, but if we use the cached geocoding
        // service we won't be wasting much resources when using a naive
        // approach like this.
        $command = new UpdateGeoCoordinatesFromAddress(
            $majorInfoUpdated->getPlaceId(),
            $majorInfoUpdated->getAddress()
        );

        $this->commandBus->dispatch($command);
    }

    /**
     * @param ActorImportedFromUDB2 $actorImportedFromUDB2
     */
    private function handleActorImportedFromUDB2(ActorImportedFromUDB2 $actorImportedFromUDB2)
    {
        $actor = ActorItemFactory::createActorFromCdbXml(
            $actorImportedFromUDB2->getCdbXmlNamespaceUri(),
            $actorImportedFromUDB2->getCdbXml()
        );

        $contactInfo = $actor->getContactInfo();

        // Do nothing if no contact info is found.
        if (!$contactInfo) {
            return;
        }

        // Get all physical locations from the list of addresses.
        $addresses = array_map(
            function (CultureFeed_Cdb_Data_Address $address) {
                return $address->getPhysicalAddress();
            },
            $contactInfo->getAddresses()
        );

        // Filter out addresses without physical location.
        $addresses = array_filter($addresses);

        // Do nothing if no address is found.
        if (empty($addresses)) {
            return;
        }

        /* @var \CultureFeed_Cdb_Data_Address_PhysicalAddress $cdbAddress */
        $cdbAddress = $addresses[0];

        // Do nothing if the address already has coordinates in cdbxml.
        if (!empty($cdbAddress->getGeoInformation())) {
            return;
        }

        try {
            // Convert the cdbxml address to a udb3 address.
            $address = $this->addressFactory->fromCdbAddress($addresses[0]);
        } catch (\InvalidArgumentException $e) {
            // If conversion failed, log an error and do nothing.
            $this->logger->error(
                'Could not convert a cdbxml address to a udb3 address for geocoding.',
                [
                    'placeId' => $actorImportedFromUDB2->getActorId(),
                    'error' => $e->getMessage(),
                ]
            );
            return;
        }

        // We don't know if the address has actually been updated because
        // ActorImportedFromUDB2 is too coarse, but if we use the cached
        // geocoding service we won't be wasting much resources when using
        // a naive approach like this.
        $command = new UpdateGeoCoordinatesFromAddress(
            $actorImportedFromUDB2->getActorId(),
            $address
        );

        $this->commandBus->dispatch($command);
    }
}
