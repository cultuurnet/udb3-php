<?php

namespace CultuurNet\UDB3\Event;

use Broadway\Repository\RepositoryInterface;
use CultuurNet\Geocoding\GeocodingServiceInterface;
use CultuurNet\UDB3\Address\AddressFormatterInterface;
use CultuurNet\UDB3\CommandHandling\Udb3CommandHandler;
use CultuurNet\UDB3\Event\Commands\UpdateGeoCoordinatesFromAddress;

class GeoCoordinatesCommandHandler extends Udb3CommandHandler
{
    /**
     * @var RepositoryInterface
     */
    private $eventRepository;

    /**
     * @var AddressFormatterInterface
     */
    private $defaultAddressFormatter;

    /**
     * @var AddressFormatterInterface
     */
    private $fallbackAddressFormatter;

    /**
     * @var GeocodingServiceInterface
     */
    private $geocodingService;

    /**
     * @param RepositoryInterface $eventRepository
     * @param AddressFormatterInterface $defaultAddressFormatter
     * @param AddressFormatterInterface $fallbackAddressFormatter
     * @param GeocodingServiceInterface $geocodingService
     */
    public function __construct(
        RepositoryInterface $eventRepository,
        AddressFormatterInterface $defaultAddressFormatter,
        AddressFormatterInterface $fallbackAddressFormatter,
        GeocodingServiceInterface $geocodingService
    ) {
        $this->eventRepository = $eventRepository;
        $this->defaultAddressFormatter = $defaultAddressFormatter;
        $this->fallbackAddressFormatter = $fallbackAddressFormatter;
        $this->geocodingService = $geocodingService;
    }

    /**
     * @param UpdateGeoCoordinatesFromAddress $updateGeoCoordinates
     */
    public function handleUpdateGeoCoordinatesFromAddress(UpdateGeoCoordinatesFromAddress $updateGeoCoordinates)
    {
        $coordinates = $this->geocodingService->getCoordinates(
            $this->defaultAddressFormatter->format(
                $updateGeoCoordinates->getAddress()
            )
        );

        if ($coordinates === null) {
            $coordinates = $this->geocodingService->getCoordinates(
                $this->fallbackAddressFormatter->format(
                    $updateGeoCoordinates->getAddress()
                )
            );
        }

        /** @var Event $event */
        $event = $this->eventRepository->load($updateGeoCoordinates->getItemId());
        $event->updateGeoCoordinates($coordinates);
        $this->eventRepository->save($event);
    }
}
